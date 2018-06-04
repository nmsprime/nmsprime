# NMS Prime

Open Source Provisioning and Management Software for network operators:

Provisioning: DOCSIS
- At the time we are highly focused on DOCSIS provisioning - including VoIP

IT Maintenance
- Show your IT infrastructure in real-time in topography MAP and ERD - Entity Relation Diagram
- Auto configuration of Nagios and Cacti from one database
- Generic SNMP GUI creator 

For more informations: [Official Documentation](https://devel.roetzer-engineering.com/confluence/display/NMS/NMS+PRIME)


## Architectual Concepts

NMS Prime is written with PHP 5.6, [Laravel 5](https://laravel.com/) framework and a modern, cool and responsive [Bootstrap](http://getbootstrap.com/) topic. It is tested and developed under CentOS 7 (RHEL 7).

NMS Prime is build by standard Linux tools, like
- [ISC DHCP](https://www.isc.org/downloads/dhcp/)
- [Named](https://linux.die.net/man/8/named)
- [Nagios](https://www.nagios.org/)
- [Cacti](https://www.cacti.net/index.php)

These tools are worldwide developed, approved and used. See [Design Architecture](https://devel.roetzer-engineering.com/confluence/display/NMS/Architecture+Guidelines) for more information


## Installation

### From RPM

For CentOS 7 (RHEL 7):

```
curl -vsL https://raw.githubusercontent.com/schmto/nmsprime/dev/INSTALL-REPO.sh | bash
yum install nmsprime-*
```

### From source code:

```
curl -vsL https://raw.githubusercontent.com/schmto/nmsprime/dev/INSTALL-REPO.sh | bash
sed -i 's/\[nmsprime\]/\[nmsprime\]\nexclude=nmsprime*/' /etc/yum.repos.d/nmsprime.repo
yum clean all && yum update -y
yum install git composer
git clone https://github.com/schmto/nmsprime.git /var/www/nmsprime
cd /var/www/nmsprime
./install-from-git.sh -y
```

For more Informations [Installation](https://devel.roetzer-engineering.com/confluence/display/NMS/Installation)


---

## Contributing

Please read [CONTRIBUTING](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

* [Report a bug](https://devel.roetzer-engineering.com/confluence/display/NMS/Report+a+Bug)
* [Ticket-System](https://devel.roetzer-engineering.com/confluence/display/NMS/Open+Tickets+Overview)
* [Versioning](https://devel.roetzer-engineering.com/confluence/display/NMS/Versioning+Schema)


---

## Supporters

We thanks to the following supporters for helping and funding NMS Prime development. If you are interested in becoming a supporter, please read [here](https://devel.roetzer-engineering.com/confluence/pages/viewpage.action?pageId=6554183):

- **[Rötzer Engineering](https://roetzer-engineering.com)**
- **[ERZNET](http://erznet.tv)**
- **[MEK Cable](http://mek-cable.de)**
- **[KM3](https://km3.de)**

## Authors

* **Torsten Schmidt** - *Initial work* - [torsten](https://github.com/schmto)
* **Nino Ryschawy** - *Maintainer/Developer* - [nino](https://github.com/NinoRy)
* **Ole Ernst** - *Maintainer/Developer* - [ole](https://github.com/olebowle)
* **Patrick Reichel** - *Sub-Maintainer/Developer for ..* - [xee8ai](https://github.com/xee8ai)
* **Christian Schramm** - *Developer for ..* - [christian](https://github.com/cschra)
* **Sven Arndt** - *Developer for ..* - [sven](https://github.com/todo)

See also the list of [contributors](https://github.com/schmto/nms-prime/contributors) who participated in this project.

---

## License

This project is licensed under the GPLv3 License - see the [LICENSE](LICENSE.md) file for details. For more informations: [License Article](https://devel.roetzer-engineering.com/confluence/display/NMS/License)
