/* ---------- Incluides ---------- */
#include <stdio.h>
#include <mysql.h>
#include <string.h>
#include <stdlib.h>
#include <my_global.h>
#include <net-snmp/net-snmp-config.h>
#include <net-snmp/net-snmp-includes.h>

/* ---------- Check-Ups ---------- */
#ifdef HAVE_WINSOCK_H
#include <winsock.h>
#endif
/* ---------- Defines ---------- */

/* ---------- Global Variables ---------- */
int active_hosts, num_rows;
int reps = 9, non_reps = 5;
int requestRetries = 2, requestTimeout = 5000000;
MYSQL_RES *result;

/* ---------- Global Structures ---------- */
typedef enum pass
{
    NON_REP,
    DOWNSTREAM,
    UPSTREAM,
    FINISH
} pass_t;
/* a list of variables to query for */
struct oid_s
{
    pass_t run;
    const char *Name;
    oid Oid[MAX_OID_LEN];
    size_t OidLen;
} oids[] = {
    {NON_REP, "1.3.6.1.2.1.1.1"},                     // SysDescr
    {NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.3"},        // # US Power (2.0)
    {NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.12"},       // # T3 Timeout
    {NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.13"},       // # T4 Timeout
    {NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.17"},       // # PreEq
    {DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.1.1.6"},     // # Power
    {DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.3"},     // # Corrected
    {DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.4"},     // # Uncorrectable
    {DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.5"},     // # SNR (2.0)
    {DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.6"},     // # Microreflections
    {DOWNSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.24.1.1"}, // # SNR (3.0)
    {UPSTREAM, "1.3.6.1.2.1.10.127.1.1.2.1.3"},       // # Bandwidth
    {UPSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.2.1.1"},    // # Power (3.0)
    {UPSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.2.1.9"},    // # Ranging Status
    {FINISH}};

/* poll all hosts in parallel */
typedef struct session
{
    struct snmp_session *sess; /* SNMP session data */
    struct oid_s *currentOid;  /* How far in our poll are we */
} session_t;

/* ---------- Functions ---------- */
void initialize(void)
{
    struct oid_s *currentOid = oids;

    /* Win32: init winsock */
    SOCK_STARTUP;

    /* initialize library */
    init_snmp("asynchapp");

    /* parse the oids */
    while (currentOid->run < FINISH)
    {
        currentOid->OidLen = MAX_OID_LEN;
        if (!snmp_parse_oid(currentOid->Name, currentOid->Oid, &currentOid->OidLen))
        {
            snmp_perror("read_objid");
            printf("Could not Parse OID: %s\n", currentOid->Name);
            exit(1);
        }

        currentOid++;
    }
}

/*****************************************************************************/

void connectToMySql(void)
{
    MYSQL *con = mysql_init(NULL);
    char host[16] = "localhost";
    char user[16] = "cactiuser";
    char pass[16] = "secret";
    char db[16] = "cacti";

    if (con == NULL)
    {
        fprintf(stderr, "%s\n", mysql_error(con));
        exit(1);
    }

    if (mysql_real_connect(con, host, user, pass, db, 0, NULL, 0) == NULL)
    {
        fprintf(stderr, "%s\n", mysql_error(con));
        mysql_close(con);
        exit(1);
    }

    if (mysql_query(con, "SELECT hostname, snmp_community FROM host WHERE hostname LIKE 'cm-%' ORDER BY hostname"))
    {
        fprintf(stderr, "%s\n", mysql_error(con));
    }

    result = mysql_store_result(con);

    num_rows = mysql_num_rows(result);

    mysql_close(con);
}

/*****************************************************************************/

/*
 * simple printing of returned data
 */
int print_result(int status, struct snmp_session *sp, struct snmp_pdu *responseData)
{
    char buf[1024];
    struct variable_list *vp;
    int ix;
    struct timeval now;
    struct timezone tz;
    struct tm *tm;

    gettimeofday(&now, &tz);
    tm = localtime(&now.tv_sec);
    fprintf(stdout, "%.2d|%.2d|%.2d.%.6d: ", tm->tm_hour, tm->tm_min, tm->tm_sec,
            now.tv_usec);
    switch (status)
    {
    case STAT_SUCCESS:
        vp = responseData->variables;
        if (responseData->errstat == SNMP_ERR_NOERROR)
        {
            while (vp)
            {
                snprint_variable(buf, sizeof(buf), vp->name, vp->name_length, vp);
                fprintf(stdout, "%s: %s\n", sp->peername, buf);
                vp = vp->next_variable;
            }
        }
        else
        {
            for (ix = 1; vp && ix != responseData->errindex; vp = vp->next_variable, ix++)
                ;
            if (vp)
                snprint_objid(buf, sizeof(buf), vp->name, vp->name_length);
            else
                strcpy(buf, "(none)");
            fprintf(stdout, "%s: %s: %s\n",
                    sp->peername, buf, snmp_errstring(responseData->errstat));
        }
        return 1;
    case STAT_TIMEOUT:
        fprintf(stdout, "%s: Timeout\n", sp->peername);
        return 0;
    case STAT_ERROR:
        snmp_perror(sp->peername);
        return 0;
    }
    return 0;
}

/*****************************************************************************/
netsnmp_variable_list *getLastVarBiniding(netsnmp_variable_list *varlist)
{
    while (varlist)
    {
        if (!varlist->next_variable)
            return varlist;
        varlist = varlist->next_variable;
    }
}

/*
 * response handler
 */
int asynch_response(int operation, struct snmp_session *sp, int reqid,
                    struct snmp_pdu *responseData, void *magic)
{
    struct session *host = (struct session *)magic;
    struct snmp_pdu *request;

    if (operation == NETSNMP_CALLBACK_OP_RECEIVED_MESSAGE)
    {
        if (print_result(STAT_SUCCESS, host->sess, responseData))
        {
            netsnmp_variable_list *varlist = responseData->variables;
            int root, upstream = 0;

            varlist = getLastVarBiniding(varlist);
            host->currentOid--;

            switch (host->currentOid->run)
            {
            case NON_REP:
                host->currentOid++;

                request = snmp_pdu_create(SNMP_MSG_GETBULK);
                request->non_repeaters = 0;
                request->max_repetitions = reps;

                while (host->currentOid->run == DOWNSTREAM)
                {
                    snmp_add_null_var(request, host->currentOid->Oid, host->currentOid->OidLen);
                    host->currentOid++;
                }

                if (snmp_send(host->sess, request))
                {
                    return 1;
                }
                else
                {
                    snmp_perror("snmp_send");
                    snmp_free_pdu(request);
                }
                break;
            case DOWNSTREAM:
                root = memcmp(host->currentOid->Oid, varlist->name, (host->currentOid->OidLen) * sizeof(oid));

                if (root == 0)
                {
                    host->currentOid = host->currentOid - 5;
                    request = snmp_pdu_create(SNMP_MSG_GETBULK);
                    request->non_repeaters = 0;
                    request->max_repetitions = reps;

                    while (host->currentOid->run == DOWNSTREAM)
                    {
                        host->currentOid->Oid[host->currentOid->OidLen] = varlist->name[varlist->name_length - 1];
                        snmp_add_null_var(request, host->currentOid->Oid, host->currentOid->OidLen + 1);
                        host->currentOid++;
                    }

                    if (snmp_send(host->sess, request))
                    {
                        return 1;
                    }
                    else
                    {
                        snmp_perror("snmp_send");
                        snmp_free_pdu(request);
                    }
                }
                else
                {
                    host->currentOid++;
                    upstream = 1;
                    break;
                }
            default:
                break;
            }

            if (upstream == 0)
                host->currentOid++;

            if (host->currentOid->run == UPSTREAM)
            {
                request = snmp_pdu_create(SNMP_MSG_GETBULK);
                request->non_repeaters = 0;
                request->max_repetitions = reps;

                while (host->currentOid->run == UPSTREAM)
                {
                    snmp_add_null_var(request, host->currentOid->Oid, host->currentOid->OidLen);
                    host->currentOid++;
                }

                if (snmp_send(host->sess, request))
                {
                    return 1;
                }
                else
                {
                    snmp_perror("snmp_send");
                    snmp_free_pdu(request);
                }
            }
        }
    }
    else
        print_result(STAT_TIMEOUT, host->sess, responseData);

    /* something went wrong (or end of variables)
   * this host not active any more
   */
    active_hosts--;
    return 1;
}
/*****************************************************************************/

void asynchronous(void)
{
    int i;
    struct session *hostStatePointer;
    MYSQL_ROW currentHost;
    session_t allHosts[num_rows];

    /* startup all hosts */
    for (hostStatePointer = allHosts; (currentHost = mysql_fetch_row(result)); hostStatePointer++)
    {
        struct snmp_pdu *request;
        struct oid_s *currentOid = oids;
        struct snmp_session newSnmpSocket;

        snmp_sess_init(&newSnmpSocket); /* initialize session */
        newSnmpSocket.version = SNMP_VERSION_2c;
        newSnmpSocket.retries = requestRetries;
        newSnmpSocket.timeout = requestTimeout;
        newSnmpSocket.peername = strdup(currentHost[0]);
        newSnmpSocket.community = strdup(currentHost[1]);
        newSnmpSocket.community_len = strlen(newSnmpSocket.community);
        newSnmpSocket.callback = asynch_response; /* default callback */
        newSnmpSocket.callback_magic = hostStatePointer;

        request = snmp_pdu_create(SNMP_MSG_GETBULK); /* send the first GET */
        request->non_repeaters = non_reps;
        request->max_repetitions = 0;

        while (currentOid->run == NON_REP)
        {
            snmp_add_null_var(request, currentOid->Oid, currentOid->OidLen);
            currentOid++;
        }

        if (!(hostStatePointer->sess = snmp_open(&newSnmpSocket)))
        {
            snmp_perror("snmp_open");
            continue;
        }
        hostStatePointer->currentOid = currentOid;

        if (snmp_send(hostStatePointer->sess, request))
            active_hosts++;
        else
        {
            snmp_perror("snmp_send");
            snmp_free_pdu(request);
        }
    }

    /* async event loop - loops while any active hosts */
    while (active_hosts)
    {
        int fds = 0, block = 1;
        struct timeval timeout;
        netsnmp_large_fd_set fdset;
        //fd_set fdset; // not used due to large amount of Hosts

        //FD_ZERO(&fdset);
        //NETSNMP_LARGE_FD_ZERO(&fdset);

        //snmp_select_info(&fds, &fdset, &timeout, &block);
        snmp_sess_select_info2(NULL, &fds, &fdset, &timeout, &block);

        //fds = select(fds, &fdset, NULL, NULL, block ? NULL : &timeout);
        fds = netsnmp_large_fd_set_select(fds, &fdset, NULL, NULL, block ? NULL : &timeout);

        if (fds < 0)
        {
            perror("select failed");
            exit(1);
        }

        if (fds)
            // snmp_read(&fdset);
            snmp_read2(&fdset);
        else
            snmp_timeout();
    }

    /* cleanup */
    for (hostStatePointer = allHosts, i = 0; i < num_rows; hostStatePointer++, i++)
    {
        if (hostStatePointer->sess)
            snmp_close(hostStatePointer->sess);
    }
}

/*****************************************************************************/

int main(int argc, char **argv)
{
    initialize();

    connectToMySql();

    asynchronous();

    mysql_free_result(result);

    return 0;
}
