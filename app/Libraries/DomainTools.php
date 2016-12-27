<?php
namespace App\Libraries;

use Cache;

class DomainTools
{
    public static function getServerIP()
    {
        return Cache::remember('server_ip', 120, function() {
            // This doesnt work when offline.  TODO: fix that
            return trim(file_get_contents('http://icanhazip.com/'));
        });
    }
    
    
    // TODO: Is $forWhatHost even needed?  I don't think so...
    public static function is_IP_an_A_record($domainToParse, $ipToLookFor, $forWhatHost='', &$response=NULL)
    {
        $ret_val = false;
        $response = 'Failed';
        if (empty($forWhatHost))
            $forWhatHost = $domainToParse;
        $records = DomainTools::get_A_records($domainToParse);
        if (is_array($records))
        {
            $response = 'An A record for "'.$forWhatHost.'" does not exist pointing to the IP '.$ipToLookFor;
            foreach ($records as $host => $ips)
            {
                if ($host == $forWhatHost && in_array($ipToLookFor,$ips))
                {
                    $response = 'Success';
                    $ret_val = true;
                    break;
                }
            }
        }
        else
        {
            $response = $records;
        }
        return $ret_val;
    }
    
    public static function is_IP_an_MX_record($domainToParse, $ipToLookFor, &$response=NULL)
    {
        $ret_val = false;
        $response = 'Failed';
        $results = dns_get_record($domainToParse, DNS_ALL);
        if (count($results) == 0)
            $response = 'Invalid domain';
        foreach ($results as $record)
        {
            if ($record['type'] == 'MX' && $record['host'] == $domainToParse)
            {
                $system = $record['target'];
                $mx_lookup = DomainTools::is_IP_an_A_record($system, $ipToLookFor);
                if ($mx_lookup)
                {
                    $response = 'Success';
                    $ret_val = true;
                    break;
                }
            }
        }
        if ($response == 'Failed')
        {
            $response = 'An MX record for "'.$domainToParse.'" does not exist pointing to the IP '.$ipToLookFor;
        }
        return $ret_val;
    }
    
    public static function get_A_records($domain)
    {
        $results = dns_get_record($domain, DNS_ALL);
        if (count($results) == 0)
            return 'Invalid domain: '.$domain;
        $return = [];
        foreach ($results as $record)
        {
            if ($record['type'] == 'A')
            {
                if (!isset($return[$record['host']]))
                    $return[$record['host']] = [];
                $return[$record['host']][] = $record['ip'];
            }
        }
        return $return;
    }
    public static function get_TXT_records($domain)
    {
        $results = dns_get_record($domain, DNS_ALL);
        if (count($results) == 0)
            return 'Invalid domain';
        $return = [];
        foreach ($results as $record)
        {
            if ($record['type'] == 'TXT')
            {
                if (!isset($return[$record['host']]))
                    $return[$record['host']] = [];
                $return[$record['host']][] = $record['txt'];
            }
        }
        return $return;
    }
    
    
    public static function is_IP_in_SPF_record($domain, $ip, &$response=NULL)
    {
        $spf_record = DomainTools::get_SPF_record($domain);
        if (empty($spf_record))
        {
            $response = 'No SPF key found for the domain "'.$domain.'"';
            return false; // Maybe this should return true since it would bypass spam filters?  Oh well, we use $response anyway
        }
        //print("Got the SPF record: " . $spf_record."<br />");
        $parts = explode(' ', $spf_record);
        array_shift($parts); // get rid of first v=spf1 part
        $result = false;
        $failType = "";
        foreach ($parts as $part)
        {
            $result |= DomainTools::parse_SPF($part, true, $failType, $domain, $ip);
        }
        $response = $failType;
        return $result;
    }
    
    public static function get_SPF_record($domain)
    {
        $txt_records = DomainTools::get_TXT_records($domain);
        if (!array_key_exists($domain, $txt_records))
            return '';
        foreach ($txt_records[$domain] as $record)
        {
            if (strpos($record, 'v=spf1') === 0) // We don't look for Sender ID, should we?  Starts with "spf2.0/pra"
                return $record;
        }
        return '';
    }
    
    // TODO: Deal with ipv6
    public static function parse_SPF($part_to_check, $checkFailType, &$failType, $target_domain, $searchIP)
    {
        if ($part_to_check == "mx")
        {
            //echo "Checking MX record because found 'mx'...<br />";
            return DomainTools::is_IP_an_MX_record($target_domain, $searchIP);
        }
        elseif ($part_to_check == "a")
        {
            //echo "Checking A records because found 'a'...<br />";
            return DomainTools::is_IP_an_A_record($target_domain, $searchIP);
        }
        elseif (strpos($part_to_check, 'ip4:') === 0)
        {
            //echo "Checking IP CIDR because found '".$part_to_check."'...<br />";
            $ip = explode(':', $part_to_check)[1];
            $ret= DomainTools::cidr_match($searchIP, $ip);
            //echo "CIDR Result: " . ($ret?'true':'false')."<br />";
            return $ret;
        }
        elseif (strpos($part_to_check, 'a:') === 0)
        {
            //echo "Checking A record because found '".$part_to_check."'... <br />";
            $hostname = explode(':', $part_to_check)[1];
            return DomainTools::is_IP_an_A_record($hostname, $searchIP);
        }
        elseif (in_array($part_to_check, ['-all', '~all', '?all']))
        {
            if ($checkFailType)
            {
                if ($part_to_check == '-all')
                    $failType = 'Hard fail is set!  If this SPF check did not pass, then you will be caught by spam filters!';
                elseif ($part_to_check == '~all')
                    $failType = 'Soft fail is set.  If this SPF check did not pass, you have a 50/50 chance of being caught by spam filters.';
                elseif ($part_to_check == '?all')
                    $failType = 'Neutral fail is set.  Good chance to bypass spam filters.';
                else
                    $failType = 'Unknown: '. $part_to_check;
            }
            //echo "Got the fail type! " . $failType;
        }
        elseif (strpos($part_to_check, 'include:') === 0)
        {
            $hostname = explode(':', $part_to_check)[1];
            //echo 'Found include for "'.$hostname.'"...going down that rabbit hole now!<br />';
            $spf_record = DomainTools::get_SPF_record($hostname);
            if (empty($spf_record))
            {
                //echo 'No spf record for '.$hostname.'!<br />';
                return false;
            }
            //print("Got the SPF record for '".$hostname."': " . $spf_record.'<br />');
            $parts = explode(' ', $spf_record);
            array_shift($parts); // get rid of first v=spf1 part
            $result = false;
            $newfailType = "";
            foreach ($parts as $part)
            {
                $result |= DomainTools::parse_SPF($part, false, $newfailType, $hostname, $searchIP);
            }
            //print("Finally finished that include, result was: " . $result."<br />");
            return $result;
        }
        else
        {
            //echo "Unknown SPF option: '".$part_to_check."'...<br />";
            return false;
        }
    }
    
    public static function cidr_match($ip, $cidr)
    {
        if (strpos($cidr,'/') === false)
        {
            return $ip == $cidr;
        }
        list($subnet, $mask) = explode('/', $cidr);
    
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
        { 
            return true;
        }
    
        return false;
    }
}

