Summary: A web-based application for reporting netflow traffic usage for a small LAN.
Name: flattraffic
Version: 1.0.0
Release: 1.beta.1%{dist}
License: AGPLv3
URL: http://projects.jethrocarr.com/p/oss-flattraffic
Group: Applications/Internet
Source0: flattraffic-%{version}.tar.bz2

BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch
BuildRequires: gettext

%description
Flattraffic is a web-based application for reporting on netflow traffic usage for a small LAN.

%package www
Summary: flattraffic web-based interface and API components
Group: Applications/Internet

Requires: httpd, mod_ssl
Requires: php >= 5.3.0, mysql-server, php-mysql
Prereq: httpd, php, mysql-server, php-mysql

%description www
Provides the flattraffic web-based interface.


%package flowd
Summary:  Integration components for flowd collector.
Group: Applications/Internet

Requires: flowd
Requires: perl, perl-DBD-MySQL

%description flowd
Components for use with flowd.

%prep
%setup -q -n flattraffic-%{version}

%build


%install
rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/flattraffic/
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/flattraffic/

# install application files and resources
cp -pr * $RPM_BUILD_ROOT%{_datadir}/flattraffic/


# install configuration file
install -m0700 htdocs/include/sample-config.php $RPM_BUILD_ROOT%{_sysconfdir}/flattraffic/config.php
ln -s %{_sysconfdir}/flattraffic/config.php $RPM_BUILD_ROOT%{_datadir}/flattraffic/htdocs/include/config-settings.php

# install linking config file
install -m755 htdocs/include/config.php $RPM_BUILD_ROOT%{_datadir}/flattraffic/htdocs/include/config.php

# install the apache configuration file
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
install -m 644 resources/flattraffic-httpdconfig.conf $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/flattraffic.conf


%post www

# Reload apache
echo "Reloading httpd..."
/etc/init.d/httpd reload

# update/install the MySQL DB
if [ $1 == 1 ];
then
	# install - requires manual user MySQL setup
	echo "Run cd %{_datadir}/flattraffic/resources/; ./autoinstall.pl to install the SQL database."
else
	# upgrade - we can do it all automatically! :-)
	echo "Automatically upgrading the MySQL database..."
	%{_datadir}/flattraffic/resources/schema_update.pl --schema=%{_datadir}/flattraffic/sql/ -v
fi



%postun www

# check if this is being removed for good, or just so that an
# upgrade can install.
if [ $1 == 0 ];
then
	# user needs to remove DB
	echo "FlatTraffic has been removed, but the MySQL database and user will need to be removed manually."
fi

%clean
rm -rf $RPM_BUILD_ROOT

%files www
%defattr(-,root,root)
%config %dir %{_sysconfdir}/flattraffic
%attr(770,root,apache) %config(noreplace) %{_sysconfdir}/flattraffic/config.php
%attr(660,root,apache) %config(noreplace) %{_sysconfdir}/httpd/conf.d/flattraffic.conf
%{_datadir}/flattraffic/htdocs
%{_datadir}/flattraffic/resources
%{_datadir}/flattraffic/sql

%doc %{_datadir}/flattraffic/README
%doc %{_datadir}/flattraffic/docs/AUTHORS
%doc %{_datadir}/flattraffic/docs/CONTRIBUTORS
%doc %{_datadir}/flattraffic/docs/COPYING

%files flowd
%{_datadir}/flattraffic/flowcollectors/flowd

%changelog
* Mon Nov 12 2012 Jethro Carr <jethro.carr@jethrocarr.com> 1.0.0_beta_1
- Inital beta public release.

