<div align="center"><a href="https://nmsprime.com"><img src="https://github.com/nmsprime/nmsprime/raw/master/public/images/nmsprime-logo.png" alt="NMS Prime Logo" title="NMS Prime - Open Source Provisioning Tool for Cable-, DOCSIS- and Broadband-Networks" width="250"/></a></div>

[![Crowdin](https://d322cqt584bo4o.cloudfront.net/nmsprime/localized.svg)](https://crowdin.com/project/nmsprime)
[![StyleCI](https://github.styleci.io/repos/109520753/shield?branch=dev)](https://github.styleci.io/repos/109520753)

# NMS PRIME – Community Version

[NMS PRIME](https://nmsprime.com) is THE Open Source Network **Provisioning Tool** and **Network Management Platform** that enables access across multiple access technologies/domains, like **DOCSIS**, **FTTH**, FTTx, **DSL** and WiFi. It allows a seamless user experience across multiple connectivity services. It reduces complexity for network operators dramatically, by a simple and easy to adapt open and enterprise **application marketplace**.

<div align="center"><a href="https://nmsprime.com"><img src="https://github.com/nmsprime/nmsprime/raw/i18n/public/images/apps_row.png" alt="NMS Prime Marketplace" title="NMS Prime Marketplace"/></a></div><br>

## Marketplace

**Community** Applications
- **OS Provisioning**
- **OS VoIP Provisioning**
- **OS Control**<br>
[.. and more](https://devel.nmsprime.com/confluence/display/NMS/Applications)

**Enterprise** Applications
- PRIME **Monitoring**
- PRIME Detect
- PRIME **Workforce**
- PRIME Ticket
- PRIME Billing
- PRIME Dashboard
- PRIME **VoIP Monitoring**<br>
[.. and many more](https://devel.nmsprime.com/confluence/display/NMS/Applications)

## Functionality
**Provisioning Tool**
- **DOCSIS** 1.0, 1.1, 2.0, **3.0, 3.1**
- **FTTH**, **DSL**, WiFi Provisioning, via **TR-069** and **RADIUS**
- IPv4 / IPv6<br>

**Network Management Platform**
- **CMTS**, OLT, **Router** and Switch Management via SNMP or TR-069
- **Cable ingress detection**
- Show and manage your IT infrastructure in real-time in **topographic maps** and entity relation diagrams
- Auto configuration of **[Icinga2](https://icinga.com/)** and **[Cacti](https://www.cacti.net/index.php)** from one database
- **Ticket System**
- Generic **SNMP GUI** creator
- Basic billing functionality
- [more informations..](https://devel.nmsprime.com/confluence/display/NMS/Applications)

For more information head over to our [Official Documentation](https://devel.nmsprime.com/confluence/display/NMS/NMS+PRIME)


## Architectural Concepts

NMS Prime is based on the [Laravel](https://laravel.com/) framework and uses [PHP 7](https://php.net) for the back end and a modern and responsive [Bootstrap](http://getbootstrap.com/) theme for the front end.

It is tested and developed under CentOS 7 (RHEL 7).

NMS Prime is build with standard Linux tools, like
- [ISC DHCP](https://www.isc.org/downloads/dhcp/) for IPv4
- [Kea](https://www.isc.org/kea/) for IPv6
- [BIND](https://linux.die.net/man/8/named)
- [Icinga2](https://icinga.com/)
- [Cacti](https://www.cacti.net/index.php)

These tools are actively developed, approved and used. See [Design Architecture](https://devel.nmsprime.com/confluence/display/NMS/Architecture+Guidelines) for more information.


## Installation

### From RPM

For CentOS 7 (RHEL 7):

**Community Version**
```bash
curl -vsL https://raw.githubusercontent.com/nmsprime/nmsprime/dev/INSTALL-REPO.sh | bash
yum install nmsprime-*
```

**Enterprise Version**

From version 3.0.0 on: Add username & password to the repository file before installing a full NMSPrime enterprise version
```bash
curl -vsL https://raw.githubusercontent.com/nmsprime/nmsprime/dev/INSTALL-REPO.sh | bash
sed -i 's/rpm\/nmsprimeOS/rpm\/nmsprimeNG/' /etc/yum.repos.d/nmsprime.repo
echo $'username=\npassword=' >> /etc/yum.repos.d/nmsprime.repo
yum install nmsprime-*
```

For the full documentation see: [Installation with RPM](https://devel.nmsprime.com/confluence/x/AYFB)

### SaaS Cloud hosted

You can run all applications in the cloud here: [Free Trial](https://www.nmsprime.com/free-trial/)

### From source code:

This is typically only recommended for developers. For a detailed walk-through see: [Installation from Source](https://devel.nmsprime.com/confluence/x/WQBs)

```bash
curl -vsL https://raw.githubusercontent.com/nmsprime/nmsprime/dev/INSTALL-REPO.sh | bash
sed -i 's/\[nmsprime\]/\[nmsprime\]\nexclude=nmsprime*/' /etc/yum.repos.d/nmsprime.repo
yum clean all && yum update -y
yum install git composer
git clone https://github.com/nmsprime/nmsprime.git /var/www/nmsprime
cd /var/www/nmsprime
./install-from-git.sh -y
```

---
## Contributors

**How to contribute**

Please read [CONTRIBUTING](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

**Write your own Application**

If you want to develop your own open-source or proprietary application(s), please refer to [Write your own Application](https://devel.nmsprime.com/confluence/x/qYJJ)

**License**

This project is licensed under the GPLv3 license - see the [LICENSE](LICENSE.md) file for details. For more information: [License Article](https://devel.nmsprime.com/confluence/display/NMS/License)
