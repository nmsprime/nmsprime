/**************************************************************************/ /*
 * NET-SNMP modempoller
 *
 * Originated from the NET-SNMP async demo
 * Hat tip to Niels Baggesen (Niels.Baggesen@uni-c.dk)
 *
 * This program retrieves a set of modems from the cacti database and queries
 * all modems for the given OIDs. The Each vendor implements the SNMP protocol
 * differently, so the program needs to check if all tables are correct and if
 * not, request another "batch".
 *
 * The requested OIDs are devided into three segments: non-repeaters for system
 * information, downstream and upstream. For each host a seperate session will
 * be created. All requests are handled asynchronously and on response the next
 * segment or the next batch of the current segment is requested.
 *
 * Christian Schramm (@cschra) and Ole Ernst (@olebowle), 2019
 *
 *****************************************************************************/
/********************************* INCLUDES **********************************/
#include <stdio.h>
#include <mysql.h>
#include <string.h>
#include <stdlib.h>
#include <net-snmp/net-snmp-config.h>
#include <net-snmp/net-snmp-includes.h>

/********************************** DEFINES **********************************/
#define MAX_REPETITIONS 9
#define RETRIES 5
#define TIMEOUT 5000000

/****************************** GLOBAL VARIABLES *****************************/
int activeHosts, host_count, nonRepeaters, downstreamOids, upstreamOids;
MYSQL_RES *result;

/* ---------- Global Structures ---------- */
// to keep track which segment is sent
typedef enum pass { NON_REP, DOWNSTREAM, UPSTREAM, FINISH } pass_t;

// a list of variables to query for
struct oid_s {
    pass_t segment;
    const char *Name;
    oid Oid[MAX_OID_LEN];
    size_t OidLen;
} oids[] = { { NON_REP, "1.3.6.1.2.1.1.1" }, // SysDescr
             { NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.3" }, // # US Power (2.0)
             { NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.12" }, // # T3 Timeout
             { NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.13" }, // # T4 Timeout
             { NON_REP, "1.3.6.1.2.1.10.127.1.2.2.1.17" }, // # PreEq
             { DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.1.1.6" }, // # Power
             { DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.3" }, // # Corrected
             { DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.4" }, // # Uncorrectable
             { DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.5" }, // # SNR (2.0)
             { DOWNSTREAM, "1.3.6.1.2.1.10.127.1.1.4.1.6" }, // # Microreflections
             { DOWNSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.24.1.1" }, // # SNR (3.0)
             { UPSTREAM, "1.3.6.1.2.1.10.127.1.1.2.1.3" }, // # Bandwidth
             { UPSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.2.1.1" }, // # Power (3.0)
             { UPSTREAM, "1.3.6.1.4.1.4491.2.1.20.1.2.1.9" }, // # Ranging Status
             { FINISH } };

// context structure to keep track of the current request
typedef struct hostSession {
    struct snmp_session *snmpSocket; // which host is currently processed
    struct oid_s *currentOid; // which OID is or was processed
    FILE *outputFile; // to which file should the response be written to
} session_t;

/********************************* FUNCTIONS *********************************/
/*
 * Connect to the cacti MySQL Database using the mysql-c high level API.
 *
 * The result of the query is stored in the global MYSQL_RES *result Variable
 * and the amount of hosts is stored in the global int host_count Variable
 *
 * returns void
 */
void connectToMySql()
{
    MYSQL *con = mysql_init(NULL);
    char host[16] = "localhost";
    char user[16] = "cactiuser";
    char pass[16] = "secret";
    char db[16] = "cacti";

    if (con == NULL) {
        fprintf(stderr, "%s\n", mysql_error(con));
        exit(1);
    }

    if (mysql_real_connect(con, host, user, pass, db, 0, NULL, 0) == NULL) {
        fprintf(stderr, "%s\n", mysql_error(con));
        mysql_close(con);
        exit(1);
    }

    if (mysql_query(con, "SELECT hostname, snmp_community FROM host WHERE hostname LIKE 'cm-%' ORDER BY hostname")) {
        fprintf(stderr, "%s\n", mysql_error(con));
    }

    result = mysql_store_result(con);

    host_count = mysql_num_rows(result);

    mysql_close(con);
}

/*****************************************************************************/
/*
 * This function sets the prerequisorities for the polling algorithm.
 * It does several things:
 * - Opens a Socket if on a windows machine
 * - Initializes the NET-SNMP library
 * - Sets Configuration for NET-SNMP
 * - Decodes OIDs and fills OID structure
 * - Counts the number of OIDs for each segment
 *
 * returns void
 */
void initialize()
{
    activeHosts = 0;
    host_count = 0;
    nonRepeaters = 0;
    upstreamOids = 0;
    downstreamOids = 0;
    struct oid_s *currentOid = oids;

    /* initialize library */
    init_snmp("asynchapp");
    netsnmp_ds_set_int(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_OID_OUTPUT_FORMAT, NETSNMP_OID_OUTPUT_NUMERIC);

    netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_QUICK_PRINT, 1);

    netsnmp_ds_set_int(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_HEX_OUTPUT_LENGTH, 0);

    /* parse the oids */
    while (currentOid->segment < FINISH) {
        currentOid->OidLen = MAX_OID_LEN;
        if (!read_objid(currentOid->Name, currentOid->Oid, &currentOid->OidLen)) {
            snmp_perror("read_objid");
            printf("Could not Parse OID: %s\n", currentOid->Name);
            exit(1);
        }

        if (currentOid->segment == NON_REP) {
            nonRepeaters++;
        }

        if (currentOid->segment == DOWNSTREAM) {
            downstreamOids++;
        }

        if (currentOid->segment == UPSTREAM) {
            upstreamOids++;
        }
        currentOid++;
    }

    /* connect to database */
    connectToMySql();
}

/*****************************************************************************/
/*
 * Print the response into a File inside the current working directory.
 *
 * int status - state of the Response
 * session_t *hostSession - pointer to the current hostcontext structure
 * struct snmp_pdu *responseData
 *
 * returns int
 */
int processResult(int status, session_t *sp, struct snmp_pdu *responseData)
{
    char buf[1024];
    struct variable_list *vp;
    int ix;

    switch (status) {
    case STAT_SUCCESS:
        vp = responseData->variables;
        if (responseData->errstat == SNMP_ERR_NOERROR) {
            while (vp) {
                snprint_variable(buf, sizeof(buf), vp->name, vp->name_length, vp);
                fprintf(sp->outputFile, "%s\n", buf);
                vp = vp->next_variable;
            }
        } else {
            for (ix = 1; vp && ix != responseData->errindex; vp = vp->next_variable, ix++)
                ;
            if (vp)
                snprint_objid(buf, sizeof(buf), vp->name, vp->name_length);
            else
                strcpy(buf, "(none)");
            fprintf(sp->outputFile, "ERROR: %s: %s: %s\n", sp->snmpSocket->peername, buf,
                    snmp_errstring(responseData->errstat));
        }
        return 1;
    case STAT_TIMEOUT:
        fprintf(stdout, "%s: Timeout\n", sp->snmpSocket->peername);
        return 0;
    case STAT_ERROR:
        snmp_perror(sp->snmpSocket->peername);
        return 0;
    }
    return 0;
}

/*****************************************************************************/
/*
 * Due to the list character of netsnmp_variable_list it is not possible to
 * access the last element directly. This loops through all variables and
 * returns the pointer to the last element
 *
 * netsnmp_variable_list varlist
 *
 * returns netsnmp_variable_list *
 */
netsnmp_variable_list *getLastVarBiniding(netsnmp_variable_list *varlist)
{
    while (varlist) {
        if (!varlist->next_variable)
            return varlist;
        varlist = varlist->next_variable;
    }
}

/*****************************************************************************/
/*
 * Utility function as this is called multiple times over the course of the
 * program. This sends a "new" Bulk request.
 *
 * session_t *hostSession - pointer to the current hostcontext structure
 * struct snmp_pdu *request - request pdu
 *
 * returns void
 */
int sendNextBulkRequest(session_t *hostSession, struct snmp_pdu *request)
{
    if (snmp_send(hostSession->snmpSocket, request)) {
        return 1;
    } else {
        snmp_perror("snmp_send");
        snmp_free_pdu(request);
    }
}

/*****************************************************************************/
/*
 * Utility function as this is called multiple times over the course of the
 * program. This compiles the Request Package Payload.
 *
 * struct snmp_pdu *request - request pdu
 * session_t *hostSession - pointer to the current hostcontext structure
 * netsnmp_variable_list *varlist - empty variable list to fill
 * pass_t segment - determines which OIDs to add as request payload
 *
 * returns void
 */
void addPackagePayload(struct snmp_pdu *request, session_t *hostSession, netsnmp_variable_list *varlist, pass_t segment,
                       int updateOids)
{
    while (hostSession->currentOid->segment == segment) {
        if (updateOids)
            hostSession->currentOid->Oid[hostSession->currentOid->OidLen] = varlist->name[varlist->name_length - 1];

        snmp_add_null_var(request, hostSession->currentOid->Oid, hostSession->currentOid->OidLen + updateOids);

        hostSession->currentOid++;
    }
}

/*****************************************************************************/
/*
 * State machine that gets called asynchronously each time a new SNMP packet
 * arrives. It checks whether the full table was retrieved and emits a new
 * SNMP request of either the next batch of the current segment or the next
 * segment.
 *
 * int operation - state of the received mesasa
 * struct snmp_session *sp - not used as we get session from context data
 * int reqid - request id - also unused
 * struct snmp_pdu *responseData - response packet with data from modem
 * void *magic - magic pointer for context data
 *
 * returns int
 */
int asynch_response(int operation, struct snmp_session *sp, int reqid, struct snmp_pdu *responseData, void *magic)
{
    session_t *hostSession = (session_t *)magic;

    if (operation == NETSNMP_CALLBACK_OP_RECEIVED_MESSAGE) {
        if (processResult(STAT_SUCCESS, hostSession, responseData)) {
            int root = -1, upstream = 0;
            struct snmp_pdu *request;

            request = snmp_pdu_create(SNMP_MSG_GETBULK);
            request->non_repeaters = 0;
            request->max_repetitions = MAX_REPETITIONS;

            netsnmp_variable_list *varlist = responseData->variables;
            varlist = getLastVarBiniding(varlist);

            switch ((hostSession->currentOid - 1)->segment) {
            case NON_REP:
                addPackagePayload(request, hostSession, varlist, DOWNSTREAM, 0);

                if (sendNextBulkRequest(hostSession, request))
                    return 1;
                break;
            case DOWNSTREAM:
                root = memcmp((hostSession->currentOid - 1)->Oid, varlist->name,
                              ((hostSession->currentOid - 1)->OidLen) * sizeof(oid));

                if (root == 0) {
                    hostSession->currentOid = hostSession->currentOid - downstreamOids;

                    addPackagePayload(request, hostSession, varlist, DOWNSTREAM, 1);

                    if (sendNextBulkRequest(hostSession, request))
                        return 1;
                    break;
                }
            case UPSTREAM:
                if (hostSession->currentOid->segment == FINISH) {
                    root = memcmp((hostSession->currentOid - 1)->Oid, varlist->name,
                                  ((hostSession->currentOid - 1)->OidLen) * sizeof(oid));

                    if (root == 0) {
                        hostSession->currentOid = hostSession->currentOid - upstreamOids;

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
    } else
        processResult(STAT_TIMEOUT, hostSession, responseData);

    // something went wrong (or end of variables)
    // this session not active any more
    activeHosts--;
    return 1;
}

/*****************************************************************************/
/*
 * Initiates the asynchronous SNMP transfer, starting with the non-repeaters.
 * The asynch_response function gets called each time a packet is received.
 * while loop handles asynch behavior.
 *
 * returns void
 */
void asynchronous()
{
    int i;
    MYSQL_ROW currentHost;
    session_t *hostSession;
    session_t allHosts[host_count]; //one hostSession structure per Host in DB

    struct snmp_pdu *request;
    struct oid_s *currentOid = oids;

    request = snmp_pdu_create(SNMP_MSG_GETBULK); /* send the first GET */
    request->non_repeaters = nonRepeaters;
    request->max_repetitions = 0;

    while (currentOid->segment == NON_REP) {
        snmp_add_null_var(request, currentOid->Oid, currentOid->OidLen);
        currentOid++;
    }

    /* startup all hosts */
    for (hostSession = allHosts; (currentHost = mysql_fetch_row(result)); hostSession++) {
        struct snmp_session newSnmpSocket;
        struct snmp_pdu *newRequest;

        snmp_sess_init(&newSnmpSocket); /* initialize session */
        newSnmpSocket.version = SNMP_VERSION_2c;
        newSnmpSocket.retries = RETRIES;
        newSnmpSocket.timeout = TIMEOUT;
        newSnmpSocket.peername = strdup(currentHost[0]);
        newSnmpSocket.community = strdup(currentHost[1]);
        newSnmpSocket.community_len = strlen(newSnmpSocket.community);
        newSnmpSocket.callback = asynch_response; /* default callback */
        newSnmpSocket.callback_magic = hostSession;

        if (!(hostSession->snmpSocket = snmp_open(&newSnmpSocket))) {
            snmp_perror("snmp_open");
            continue;
        }
        hostSession->currentOid = currentOid;
        hostSession->outputFile = fopen(newSnmpSocket.peername, "w");

        if (snmp_send(hostSession->snmpSocket, newRequest = snmp_clone_pdu(request)))
            activeHosts++;
        else {
            snmp_perror("snmp_send");
            snmp_free_pdu(newRequest);
        }
    }

    /* async event loop - loops while any active hosts */
    while (activeHosts) {
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

        if (fds < 0) {
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
    snmp_free_pdu(request);

    for (hostSession = allHosts, i = 0; i < host_count; hostSession++, i++) {
        if (hostSession->snmpSocket)
            snmp_close(hostSession->snmpSocket);
    }
}

/*****************************************************************************/
/*
 * close all file descriptors and free the MySQL result
 *
 * returns void
 */
void cleanup()
{
    mysql_free_result(result);
    fcloseall();
}

/*****************************************************************************/
/*
 * main function
 *
 * returns int
 */
int main(int argc, char **argv)
{
    initialize();

    asynchronous();

    cleanup();

    return 0;
}
