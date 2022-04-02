# hshub dashboard

This is the source code for hshub DNS webui, this works with Mutual (API which sits on the master PowerDNS/MySQL replication server) to manipulate records.

(The documentation is a WIP, please bear with us, we are just trying to make the source available)

You will need to setup PowerDNS with a MySQL backend (probably with some sort of SQL replication), the webui is ideally hosted on one server while the PowerDNS servers sit seperately.

It is obviously recommended to lock down MySQL and have the servers communicate over a VPN/Backplane network (such as ZeroTier).

On the webui server, you will need to fetch ICANN tlds via a cronjob so that the webui knows which TLDs are ICANN and which are Handshake:
0 0 * * * /usr/bin/php mutual/etc/tlds.php >/dev/null 2>&1

The TODO list for setting up Mutual is:
* Setup sudo to access pdnsutil under the web user
* Import database for PowerDNS (which is modified for Mutual/Dashboard)

## License
[![CC BY-NC-SA](https://i.creativecommons.org/l/by-nc-nd/3.0/88x31.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
