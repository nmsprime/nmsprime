/* ---------- Incluides ---------- */
#include <stdio.h>
#include <mysql.h>
#include <string.h>
#include <stdlib.h>
#include <net-snmp/net-snmp-config.h>
#include <net-snmp/net-snmp-includes.h>

/* ---------- Check-Ups ---------- */
#ifdef HAVE_WINSOCK_H
#include <winsock.h>
#endif
/* ---------- Global Variables ---------- */
int active_hosts, num_rows;
int ds_count = 0, us_count = 0;
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
typedef struct hostSession
{
    struct snmp_session *snmpSocket; /* SNMP session data */
    struct oid_s *currentOid;        /* How far in our poll are we */
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
        if (!read_objid(currentOid->Name, currentOid->Oid, &currentOid->OidLen))
        {
            snmp_perror("read_objid");
            printf("Could not Parse OID: %s\n", currentOid->Name);
            exit(1);
        }

        if (currentOid->run == DOWNSTREAM)
        {
            ds_count++;
        }

        if (currentOid->run == UPSTREAM)
        {
            us_count++;
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

/*****************************************************************************/
int sendNextBulkRequest(session_t *hostSession, struct snmp_pdu *request)
{
    if (snmp_send(hostSession->snmpSocket, request))
    {
        return 1;
    }
    else
    {
        snmp_perror("snmp_send");
        snmp_free_pdu(request);
    }
}
/*****************************************************************************/
void addPackagePayload(struct snmp_pdu *request, session_t *hostSession, netsnmp_variable_list *varlist, pass_t run, int updateOids)
{
    while (hostSession->currentOid->run == run)
    {
        if (updateOids)
            hostSession->currentOid->Oid[hostSession->currentOid->OidLen] = varlist->name[varlist->name_length - 1];

        snmp_add_null_var(request, hostSession->currentOid->Oid, hostSession->currentOid->OidLen + updateOids);

        hostSession->currentOid++;
    }
}
/*
 * response handler
 */
int asynch_response(int operation, struct snmp_session *sp, int reqid,
                    struct snmp_pdu *responseData, void *magic)
{
    session_t *hostSession = (session_t *)magic;

    if (operation == NETSNMP_CALLBACK_OP_RECEIVED_MESSAGE)
    {
        if (print_result(STAT_SUCCESS, hostSession->snmpSocket, responseData))
        {
            int root = -1, upstream = 0;
            struct snmp_pdu *request;

            request = snmp_pdu_create(SNMP_MSG_GETBULK);
            request->non_repeaters = 0;
            request->max_repetitions = reps;

            netsnmp_variable_list *varlist = responseData->variables;
            varlist = getLastVarBiniding(varlist);

            switch ((hostSession->currentOid - 1)->run)
            {
            case NON_REP:
                addPackagePayload(request, hostSession, varlist, DOWNSTREAM, 0);

                if (sendNextBulkRequest(hostSession, request))
                    return 1;
                break;
            case DOWNSTREAM:
                root = memcmp((hostSession->currentOid - 1)->Oid, varlist->name, ((hostSession->currentOid - 1)->OidLen) * sizeof(oid));

                if (root == 0)
                {
                    hostSession->currentOid = hostSession->currentOid - ds_count;

                    addPackagePayload(request, hostSession, varlist, DOWNSTREAM, 1);

                    if (sendNextBulkRequest(hostSession, request))
                        return 1;
                    break;
                }
            case UPSTREAM:
                if (hostSession->currentOid->run == FINISH)
                {
                    root = memcmp((hostSession->currentOid - 1)->Oid, varlist->name, ((hostSession->currentOid - 1)->OidLen) * sizeof(oid));

                    if (root == 0)
                    {
                        hostSession->currentOid = hostSession->currentOid - us_count;

                        addPackagePayload(request, hostSession, varlist, UPSTREAM, 1);

                        if (sendNextBulkRequest(hostSession, request))
                            return 1;
                    }
                    break;
                }

                addPackagePayload(request, hostSession, varlist, UPSTREAM, 0);

                if (sendNextBulkRequest(hostSession, request))
                    return 1;
                break;
            }
        }
    }
    else
        print_result(STAT_TIMEOUT, hostSession->snmpSocket, responseData);

    // something went wrong (or end of variables)
    // this session not active any more
    active_hosts--;
    return 1;
}
/*****************************************************************************/

void asynchronous(void)
{
    int i;
    MYSQL_ROW currentHost;
    session_t *hostSession;
    session_t allHosts[num_rows]; //one hostSession structure per Host in DB

    /* startup all hosts */
    for (hostSession = allHosts; (currentHost = mysql_fetch_row(result)); hostSession++)
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
        newSnmpSocket.callback_magic = hostSession;

        request = snmp_pdu_create(SNMP_MSG_GETBULK); /* send the first GET */
        request->non_repeaters = non_reps;
        request->max_repetitions = 0;

        while (currentOid->run == NON_REP)
        {
            snmp_add_null_var(request, currentOid->Oid, currentOid->OidLen);
            currentOid++;
        }

        if (!(hostSession->snmpSocket = snmp_open(&newSnmpSocket)))
        {
            snmp_perror("snmp_open");
            continue;
        }
        hostSession->currentOid = currentOid;

        if (snmp_send(hostSession->snmpSocket, request))
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
    for (hostSession = allHosts, i = 0; i < num_rows; hostSession++, i++)
    {
        if (hostSession->snmpSocket)
            snmp_close(hostSession->snmpSocket);
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
