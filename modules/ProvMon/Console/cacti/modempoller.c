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

/* ---------- Global Structures ---------- */
/* a list of hosts to query*/
struct host
{
    const char *name;
    const char *community;
} hosts[] = {
    {"test1", "public"},
    {"test2", "public"},
    {"test3", "public"},
    {"test4", "public"},
    {NULL}};

/* a list of variables to query for */
struct oid
{
    const char *Name;
    oid Oid[MAX_OID_LEN];
    int OidLen;
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
        op++;
    }
}

/*
 * simple printing of returned data
 */
int print_result(int status, struct snmp_session *sp, struct snmp_pdu *pdu)
{
    char buf[1024];
    struct variable_list *vp;
    int ix;
    struct timeval now;
    struct timezone tz;
    struct tm *tm;

    gettimeofday(&now, &tz);
    tm = localtime(&now.tv_sec);
    fprintf(stdout, "%.2d:%.2d:%.2d.%.6d ", tm->tm_hour, tm->tm_min, tm->tm_sec,
            now.tv_usec);
    switch (status)
    {
    case STAT_SUCCESS:
        vp = pdu->variables;
        if (pdu->errstat == SNMP_ERR_NOERROR)
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
            for (ix = 1; vp && ix != pdu->errindex; vp = vp->next_variable, ix++)
                ;
            if (vp)
                snprint_objid(buf, sizeof(buf), vp->name, vp->name_length);
            else
                strcpy(buf, "(none)");
            fprintf(stdout, "%s: %s: %s\n",
                    sp->peername, buf, snmp_errstring(pdu->errstat));
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
 * poll all hosts in parallel
 */
struct session
{
    struct snmp_session *sess; /* SNMP session data */
    struct oid *current_oid;   /* How far in our poll are we */
} sessions[sizeof(hosts) / sizeof(hosts[0])];

int active_hosts; /* hosts that we have not completed */

/*****************************************************************************/

/*
 * response handler
 */
int asynch_response(int operation, struct snmp_session *sp, int reqid,
                    struct snmp_pdu *pdu, void *magic)
{
    struct session *host = (struct session *)magic;
    struct snmp_pdu *req;

    if (operation == NETSNMP_CALLBACK_OP_RECEIVED_MESSAGE)
    {
        if (print_result(STAT_SUCCESS, host->sess, pdu))
        {
            host->current_oid++; /* send next GET (if any) */
            if (host->current_oid->Name)
            {
                req = snmp_pdu_create(SNMP_MSG_GET);
                snmp_add_null_var(req, host->current_oid->Oid, host->current_oid->OidLen);
                if (snmp_send(host->sess, req))
                    return 1;
                else
                {
                    snmp_perror("snmp_send");
                    snmp_free_pdu(req);
                }
            }
        }
    }
    else
        print_result(STAT_TIMEOUT, host->sess, pdu);

    /* something went wrong (or end of variables)
   * this host not active any more
   */
    active_hosts--;
    return 1;
}
/*****************************************************************************/

void asynchronous(void)
{
    struct session *hs;
    struct host *hp;

    /* startup all hosts */

    for (hs = sessions, hp = hosts; hp->name; hs++, hp++)
    {
        struct snmp_pdu *req;
        struct snmp_session sess;
        snmp_sess_init(&sess); /* initialize session */
        sess.version = SNMP_VERSION_2c;
        sess.peername = strdup(hp->name);
        sess.community = strdup(hp->community);
        sess.community_len = strlen(sess.community);
        sess.callback = asynch_response; /* default callback */
        sess.callback_magic = hs;
        if (!(hs->sess = snmp_open(&sess)))
        {
            snmp_perror("snmp_open");
            continue;
        }
        hs->current_oid = oids;
        req = snmp_pdu_create(SNMP_MSG_GET); /* send the first GET */
        snmp_add_null_var(req, hs->current_oid->Oid, hs->current_oid->OidLen);
        if (snmp_send(hs->sess, req))
            active_hosts++;
        else
        {
            snmp_perror("snmp_send");
            snmp_free_pdu(req);
        }
    }

    /* loop while any active hosts */

    while (active_hosts)
    {
        int fds = 0, block = 1;
        fd_set fdset;
        struct timeval timeout;

        FD_ZERO(&fdset);
        snmp_select_info(&fds, &fdset, &timeout, &block);
        fds = select(fds, &fdset, NULL, NULL, block ? NULL : &timeout);
        if (fds < 0)
        {
            perror("select failed");
            exit(1);
        }
        if (fds)
            snmp_read(&fdset);
        else
            snmp_timeout();
    }

    /* cleanup */

    for (hp = hosts, hs = sessions; hp->name; hs++, hp++)
    {
        if (hs->sess)
            snmp_close(hs->sess);
    }
}

/*****************************************************************************/

int main(int argc, char **argv)
{
    initialize();

    asynchronous();

    return 0;
}
