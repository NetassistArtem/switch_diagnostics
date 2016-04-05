<?php


class Connect_SNMP
{
    private $snmp_session;
    private $version = SNMP::VERSION_2c;
    private $community_read;
    private $community_write;


    public function __construct($switch_ip, $wr_rd = 'r')
    {
        $this->community_read = Config::get('community_read');
        $this->community_write = Config::get('community_write');
        if($wr_rd == 'w'){
            $community = $this->community_write;
        }elseif($wr_rd == 'r'){
            $community = $this->community_read;
        }else{
            throw new Exception('Wrong community flag', 500);
        }
        $this->snmp_session = new SNMP($this->version, $switch_ip, $community);
        $this->snmp_session->exceptions_enabled = SNMP::ERRNO_ANY;
        $this->snmp_session->valueretrieval = SNMP_VALUE_PLAIN;
    }

    public function close_session()
    {
        $this->snmp_session->close();
    }

    public function getByKey($key)
    {
        $data = $this->snmp_session->get($key, $preserve_keys = true);
        $this->close_session();

        return $data;
    }

    public function setData($object_id,$type,$value)
    {
        $this->snmp_session->set($object_id,$type,$value);
        $this->close_session();

    }

    public function walkByKey($key)
    {
       $data = $this->snmp_session->walk($key);
        $this->close_session();

        return $data;
    }

    /**
     * @return SNMP
     */
    public function getSnmpSession()
    {
        return $this->snmp_session;
    }





}

