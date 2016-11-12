<?php
namespace App\Libraries;

class DomainTools
{
    public static function get_A_records($domain)
    {
        $results = dns_get_record($domain, DNS_ALL);
        if (count($results) == 0)
            return 'Invalid domain';
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
    
    
    public static function get_SPF_record($domain)
    {
        $txt_records = DomainTools::get_TXT_records($domain);
    }
    
    public static function parse_SPF($first_record, $target_domain)
    {
        
    }
    
    public static function cidr_match($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
    
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
        { 
            return true;
        }
    
        return false;
    }
}

