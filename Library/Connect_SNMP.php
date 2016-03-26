<?php


class Connect_SNMP
{
    private $snmp_session;
    private $version = SNMP::VERSION_2c;
    private $community;

    public function __construct($switch_ip)
    {
        $this->community = Config::get('community');
        $this->snmp_session = new SNMP($this->version, $switch_ip, $this->community);
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

    }

    /**
     * @return SNMP
     */
    public function getSnmpSession()
    {
        return $this->snmp_session;
    }



}

