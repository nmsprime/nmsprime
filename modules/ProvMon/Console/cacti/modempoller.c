<<<<<<< HEAD
/*
 * NET-SNMP modempoller
 *
 * Originated from the NET-SNMP async demo
 * Hat tip to Niels Baggesen (Niels.Baggesen@uni-c.dk)
 *
 */

=======
/* ---------- Incluides ---------- */
#include <stdio.h>
#include <mysql.h>
#include <string.h>
#include <stdlib.h>
#include <my_global.h>
>>>>>>> 0666391... Add includes and structural comments
#include <net-snmp/net-snmp-config.h>
#include <net-snmp/net-snmp-includes.h>

/* ---------- Check-Ups ---------- */
#ifdef HAVE_WINSOCK_H
#include <winsock.h>
#endif
/* ---------- Defines ---------- */

/* ---------- Global Variables ---------- */
int active_hosts, num_rows;
int requestRetries = 2, requestTimeout = 5000000;
MYSQL_RES *result;

/* ---------- Global Structures ---------- */
/* a list of variables to query for */
struct oid
{
    const char *Name;
    oid Oid[MAX_OID_LEN];
    oid root[MAX_OID_LEN];
    size_t OidLen;
    size_t rootlen;
} oids[] = {
    {"1.3.6.1.2.1.1.1"},                  // SysDescr
    {".1.3.6.1.2.1.10.127.1.2.2.1.3"},    // # US Power (2.0)
    {".1.3.6.1.2.1.10.127.1.2.2.1.12"},   // # T3 Timeout
    {".1.3.6.1.2.1.10.127.1.2.2.1.13"},   // # T4 Timeout
    {".1.3.6.1.2.1.10.127.1.2.2.1.17"},   // # PreEq
    {"1.3.6.1.2.1.10.127.1.1.1.1.6"},     // # Power
    {"1.3.6.1.4.1.4491.2.1.20.1.24.1.1"}, // # SNR (3.0)
    {"1.3.6.1.2.1.10.127.1.1.4.1.3"},     // # Corrected
    {"1.3.6.1.2.1.10.127.1.1.4.1.4"},     // # Uncorrectable
    {"1.3.6.1.2.1.10.127.1.1.4.1.5"},     // # SNR (2.0)
    {"1.3.6.1.2.1.10.127.1.1.4.1.6"},     // # Microreflections
    {"1.3.6.1.2.1.10.127.1.1.2.1.3"},     // # Bandwidth
    {"1.3.6.1.4.1.4491.2.1.20.1.2.1.1"},  // # Power (3.0)
    {"1.3.6.1.4.1.4491.2.1.20.1.2.1.9"},  // # Ranging Status
    {NULL}};

/* poll all hosts in parallel */
typedef struct session
{
    struct snmp_session *sess; /* SNMP session data */
    struct oid *current_oid;   /* How far in our poll are we */
} session_t;

/* ---------- Functions ---------- */
void initialize(void)
{
    struct oid *op = oids;

    /* Win32: init winsock */
    SOCK_STARTUP;

    /* initialize library */
    init_snmp("asynchapp");

    /* parse the oids */
    while (op->Name)
    {
        op->OidLen = sizeof(op->Oid) / sizeof(op->Oid[0]);
        if (!read_objid(op->Name, op->Oid, &op->OidLen))
        {
            snmp_perror("read_objid");
            exit(1);
        }

        op->rootlen = MAX_OID_LEN;
        if (snmp_parse_oid(op->Name, op->root, &op->rootlen) == NULL)
        {
            snmp_perror(op->Name);
            exit(1);
        }
        op++;
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
            host->current_oid++; /* send next GET (if any) */
            if (host->current_oid->Name)
            {
                request = snmp_pdu_create(SNMP_MSG_GETNEXT);
                snmp_add_null_var(request, host->current_oid->Oid, host->current_oid->OidLen);
                if (snmp_send(host->sess, request))
                    return 1;
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
        if (!(hostStatePointer->sess = snmp_open(&newSnmpSocket)))
        {
            snmp_perror("snmp_open");
            continue;
        }
        hostStatePointer->current_oid = oids;
        request = snmp_pdu_create(SNMP_MSG_GETNEXT); /* send the first GET */
        snmp_add_null_var(request, hostStatePointer->current_oid->Oid, hostStatePointer->current_oid->OidLen);
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
