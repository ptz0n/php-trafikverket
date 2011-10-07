<?php
/**
 * Trafikverket API wrapper
 *
 * @category  Services
 * @package   Trafikverket
 * @author    Erik Pettersson <mail@ptz0n.se>
 * @copyright 2011 Erik Pettersson <mail@ptz0n.se>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
class Trafikverket extends Service
{

    /**
     * Default cURL options
     *
     * @var array
     *
     * @access private
     * @static
     */
    protected static $_curlDefaultHeaders = array('Content-type:text/xml; charset=UTF-8');

    /**
     * API domain
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_domain = 'trafikinfo.trafikverket.se';

    /**
     * HTTP user agent
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_userAgent = 'PHP-Trafikverket';

    /**
     * Tables
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_plugins = array(
        'ATK' => array(
            'Cameras'
        ),
        'CameraInfo' => array(
            'Cameras',
        ),
        'KartDB' => array(
            'Messages',
            'Stations'
        ),
        'TrissData2' => array(
            'Deviations',
            'CurrentDeviations',
            'Weather',
            'RestAreas'
        ),
        'WOW' => array(
            'Trafiklagen',
            'LpvTrafiklagen',
            'Trains',
            'TrafiklagenJoinTrains'
        )
    );

    /**
     * Tables
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_queryAttributes = array(
        'plugin' => 'plugin',
        'table'  => 'table',
        'filter' => 'table',
        'order'  => 'orderby',
        'select' => 'selectcolumns',
        'limit'  => 'limit'
    );

    /**
     * Request query
     *
     * @var array
     *
     * @access private
     */
    protected $_query = array();

    /**
     * Class constructor
     *
     * @return void
     *
     * @access public
     */
    function __construct($locale = 'SE_sv')
    {
        parent::__construct();

        $this->_locale = $locale;

        $this->_curlOptions[CURLOPT_POST] = 1;
        $this->_curlOptions[CURLOPT_HTTPHEADER] = self::$_curlDefaultHeaders;
    }

    /**
     * Construct a URL
     *
     * @return string $url
     *
     * @access protected
     */
    protected function _buildUrl()
    {
        $url = 'http://';
        $url .= self::$_domain;
        $url .= '/litcore/orion/orionproxy.ashx';

        return $url;
    }

    /**
     * Construct post data
     *
     */
    protected function _buildRequestBody()
    {
        $body = '<ORIONML version="1.0">
    <REQUEST plugin="'.$this->_query['plugin'].'" locale="'.$this->_locale.'">
        <PLUGINML';
        foreach(self::$_queryAttributes as $key => $attr) {
            $body .= isset($this->_query[$key]) ? ' '.$attr.'="'.$this->_query[$key].'"' : '';
        }
        $body .= ' />
</REQUEST>
</ORIONML>';

        return $body;
    }

    /**
     * Construct post data
     *
     * @return boolean
     */
    protected function _validateQueryAttributes()
    {
        $return = false;

        if(isset($this->_query['plugin']) &&
            array_key_exists($this->_query['plugin'], self::$_plugins)) {
            if(isset($this->_query['table']) &&
                in_array($this->_query['table'], self::$_plugins[$this->_query['plugin']])) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Setup query attributes
     *
     * @param array $attr
     *
     * @return void
     *
     * @access protected
     */
    protected function _setupQueryAttributes($attr)
    {
        $this->_query = array(); // Empty old query

        foreach($attr as $key => $value) { // Setup all attributes
            if(in_array($key, self::$_queryAttributes)) {
                $this->_query[$key] = $value;
            }
        }

        if(!$this->_validateQueryAttributes()) {
            throw new Exception('Invalid query attributes.');
        }
    }

    /**
     * Construct post data
     *
     * @param array $attr
     *
     * @return boolean
     *
     * @access public
     */
    function query($attr)
    {
        $this->_setupQueryAttributes($attr);

        $body = $this->_buildRequestBody();
        $url = $this->_buildUrl();

        $this->_curlOptions[CURLOPT_POSTFIELDS] = $this->_buildRequestBody();

        $data = $this->_request($url, true);

        if(!isset($data->ORIONML)) {
            $table = $this->_query['table'];
            foreach($data->$table as $data) {
                return $data;
            }
        }
        else {
            throw new Exception('Service error: '.$data->ORIONML->ERROR[0]->value);
        }
    }
}