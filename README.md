<div align="center"><a href="https://nmsprime.com"><img src="https://github.com/nmsprime/nmsprime/raw/master/public/images/nmsprime-logo.png" alt="NMS Prime Logo" title="NMS Prime - Open Source Provisioning Tool for Cable-, DOCSIS- and Broadband-Networks" width="250"/></a></div>

[![Crowdin](https://d322cqt584bo4o.cloudfront.net/nmsprime/localized.svg)](https://crowdin.com/project/nmsprime)
[![StyleCI](https://github.styleci.io/repos/109520753/shield?branch=dev)](https://github.styleci.io/repos/109520753)

# NMS PRIME

[NMS PRIME](https://nmsprime.com) is THE Open Source Network Provisioning Tool and Network Management Platform specialized for Cable-, DOCSIS- and Broadband-Networks.

**Provisioning Tool**
- **Network provisioning**
- **VoIP provisioning**

**ISP Billing**
- for cable networks
- **VoIP billing** for cable modems

**Network Management Platform**
- **Cable ingress detection**
- Show your IT infrastructure in real-time in topography MAP and ERD - Entity Relation Diagram
- Auto configuration of **Icinga** and **Cacti** from one database
- **Ticket System**
- Generic SNMP GUI creator
- **CMTS** Management

For more information head over to our [Official Documentation](https://devel.roetzer-engineering.com/confluence/display/NMS/NMS+PRIME)


## Architectural Concepts

NMS Prime is based on the [Laravel](https://laravel.com/) Framework and uses [PHP 7](https://php.net) for the Back-End and a modern and responsive [Bootstrap](http://getbootstrap.com/) Theme for the Front-End.

It is tested and developed under CentOS 7 (RHEL 7).

NMS Prime is build with standard Linux tools, like
- [ISC DHCP](https://www.isc.org/downloads/dhcp/)
- [Named](https://linux.die.net/man/8/named)
- [Icinga](https://icinga.com/)
- [Cacti](https://www.cacti.net/index.php)

These tools are actively developed, approved and used. See [Design Architecture](https://devel.roetzer-engineering.com/confluence/display/NMS/Architecture+Guidelines) for more information.


## Installation

### From RPM

For CentOS 7 (RHEL 7):

```bash
curl -vsL https://raw.githubusercontent.com/nmsprime/nmsprime/dev/INSTALL-REPO.sh | bash
yum install nmsprime-*
```

### From source code:

```bash
curl -vsL https://raw.githubusercontent.com/nmsprime/nmsprime/dev/INSTALL-REPO.sh | bash
sed -i 's/\[nmsprime\]/\[nmsprime\]\nexclude=nmsprime*/' /etc/yum.repos.d/nmsprime.repo
yum clean all && yum update -y
yum install git composer
git clone https://github.com/nmsprime/nmsprime.git /var/www/nmsprime
cd /var/www/nmsprime
./install-from-git.sh -y
```

For more Information have a look at the [Installation Process](https://devel.roetzer-engineering.com/confluence/display/NMS/Installation)


---

## How to contribute

Please read [CONTRIBUTING](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.


## Contributors

See the list of [contributors](https://github.com/nmsprime/nmsprime/graphs/contributors) who participated in this project. NMS Prime is an open source project. We encourage you to [become a member](https://www.nmsprime.com/about)!

---

## Donate

If you like NMS Prime, feel free to support us.

[![](https://www.paypalobjects.com/en_US/DK/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EXGQS35VD6UVU&source=url)

---

## License

This project is licensed under the GPLv3 License - see the [LICENSE](LICENSE.md) file for details. For more informations: [License Article](https://devel.roetzer-engineering.com/confluence/display/NMS/License)
