<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Config;
use SSH;
class NSController extends Controller
{
    public function setupNS(Request $request) {

    	$this->validate($request, [
			'domain' 	=>		'required',
			'host1' 	=>		'required|ip',
			'username1'	=>		'required',
			'password1'	=>		'required',
			'host2'		=>		'required|ip',
			'username2'	=>		'required',
			'password2'	=>		'required',
		]);
		ini_set('max_execution_time', 3000);
		Config::set('remote.connections.host1', [
			'host' 		=>		$request->host1,
			'username'	=>		$request->username1,
			'password'	=>		$request->password1,
	        'timeout'   => 		10,
		]);


		Config::set('remote.connections.host2', [
			'host' 		=>		$request->host2,
			'username'	=>		$request->username2,
			'password'	=>		$request->password2,
	        'timeout'   => 		10,
		]);
		$domain = $request->domain;
		$host1 = $request->host1;
		$host2 = $request->host2;
		$replaceLineContaining = function($search, $replace, $text){
			$lines = explode(PHP_EOL, $text);
			foreach ($lines as $key => $line) {
				if(strripos($line, $search) !== false) {
					$lines[$key] = $replace;
				}
			}
			return implode(PHP_EOL, $lines);
		};
		try {
			$hosts = SSH::into('host1')->getString('/etc/hosts');
		} catch (\ErrorException $e) {
			return redirect()->back()->withErrors(['domain' => 'wrong credentials']);
		}
		$hosts = $replaceLineContaining($host1, $host1.'    ns1.'.$domain.' ns1', $hosts);
		$options_file = 'options {
        directory "/var/cache/bind";
        recursion no;
        allow-transfer { none; };

        dnssec-validation auto;

        auth-nxdomain no;    # conform to RFC1035
        listen-on-v6 { any; };
};';
		$local_file = 'zone "'.$domain.'" {
    type master;
    file "/etc/bind/zones/db.'.$domain.'";
    allow-transfer { '.$host2.'; };
};';
		$zone_file = '$TTL    604800
@       IN      SOA     ns1.example.com. admin.example.com. (
                              5         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;

; Name servers
example.com.    IN      NS      ns1.example.com.
example.com.    IN      NS      ns2.example.com.

; A records for name servers
ns1             IN      A       192.0.2.1
ns2             IN      A       192.0.2.2

; Other A records
@               IN      A       192.0.2.3
www             IN      A       192.0.2.3';
		$zone_file = str_ireplace('example.com', $domain, $zone_file);
		$zone_file = str_replace('192.0.2.1', $host1, $zone_file);
		$zone_file = str_replace('192.0.2.2', $host2, $zone_file);
		$zone_file = str_replace('192.0.2.3', $host1, $zone_file);
		$host1_commands = [
			"sudo apt-get update",
			"sudo apt-get install software-properties-common -y",
			"sudo add-apt-repository ppa:ondrej/php -y",
			"sudo apt-get update",
			"sudo dpkg --configure -a --force-confold",
			"sudo apt-get install bind9 bind9utils bind9-doc apache2 php7.2 libapache2-mod-php -y",
			"sudo apt-get install php7.2-mysql php7.2-curl php7.2-json php7.2-cgi php7.2-xsl -y",
		    "echo '".$hosts."' | tee /etc/hosts",
		    "echo 'ns1' | tee /etc/hostname",
		    "sudo hostname -F /etc/hostname",
		    "echo '$options_file' | tee /etc/bind/named.conf.options",
		    "echo '$local_file' | tee /etc/bind/named.conf.local",
		    "sudo mkdir /etc/bind/zones",
		    "echo '".$zone_file."' | tee /etc/bind/zones/db.".$domain,
		    "sudo named-checkconf",
		    "sudo named-checkzone $domain /etc/bind/zones/db.$domain",
		    "sudo service bind9 restart",
		    "echo '<?php phpinfo(); ?>' | tee /var/www/html/info.php"
		];
		foreach ($host1_commands as $key => $value) {
			SSH::into('host1')->run([$value], function($line){
				echo str_replace(PHP_EOL, '<br>', $line) .'<br>';
			});
		}
		//========================== NS2 Setup ====================
		$hosts = SSH::into('host2')->getString('/etc/hosts');
		$hosts = $replaceLineContaining($host2, $host2.'    ns2.'.$domain.' ns2', $hosts);
		//options file will be the same for ns2
		$local_file = 'zone "example.com" {
    type slave;
    file "db.example.com";
    masters { 192.0.2.1; };
};';
		$local_file = str_ireplace('example.com', $domain, $local_file);
		$local_file = str_ireplace('192.0.2.1', $host1, $local_file);
		$host2_commands = [
			"sudo apt-get update",
			"sudo dpkg --configure -a --force-confold",
			"sudo apt-get install bind9 bind9utils bind9-doc -y",
		    "echo '".$hosts."' | tee /etc/hosts",
		    "echo 'ns2' | tee /etc/hostname",
		    "sudo hostname -F /etc/hostname",
		    "echo '$options_file' | tee /etc/bind/named.conf.options",
		    "echo '$local_file' | tee /etc/bind/named.conf.local",
		    "sudo named-checkconf",
		    "sudo service bind9 restart",
		];
		// dd($host1_commands, $host2_commands);
		foreach ($host2_commands as $key => $value) {
			SSH::into('host2')->run([$value], function($line){
				echo str_replace(PHP_EOL, '<br>', $line) .'<br>';
			});
		}
		echo "$request->domain<br>$request->host1 / $request->password1<br>$request->host2 / $request->password2";exit;
    }
}
