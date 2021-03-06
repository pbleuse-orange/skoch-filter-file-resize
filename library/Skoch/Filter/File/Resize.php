<?php

/**
 * Zend Framework addition by skoch
 * 
 * @category   Skoch
 * @package    Skoch_Filter
 * @author     Stefan Koch <cct@stefan-koch.name>
 */
 
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';
 
/**
 * Resizes a given file and saves the created file
 *
 * @category   Skoch
 * @package    Skoch_Filter
 */
class Skoch_Filter_File_Resize implements Zend_Filter_Interface
{
    protected $_width = null;
    protected $_height = null;
    protected $_keepRatio = true;
    protected $_keepSmaller = true;
    protected $_directory = null;
    protected $_cropToFit = false;
    protected $_follow = false;
    protected $_jpegQuality = 75;
    protected $_pngQuality = 6;
    protected $_png8Bits = false;
    protected $_adapter = 'Skoch_Filter_File_Resize_Adapter_Gd';
 
    /**
     * Create a new resize filter with the given options
     *
     * @param Zend_Config|array $options Some options. You may specify: width, 
     * height, keepRatio, keepSmaller (do not resize image if it is smaller than
     * expected), directory (save thumbnail to another directory),
     * adapter (the name or an instance of the desired adapter),
     * jpegQuality (0 - 100 only for jpeg image),
     * pngQuality (0 - 9 only for png image),
     * png8Bits (true/false force png to 8bit)
     * @return Skoch_Filter_File_Resize An instance of this filter
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }
 
        if (!isset($options['width']) && !isset($options['height'])) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('At least one of width or height must be defined');
        }
 
        if (isset($options['width'])) {
            $this->_width = $options['width'];
        }
        if (isset($options['height'])) {
            $this->_height = $options['height'];
        }
        if (isset($options['keepRatio'])) {
            $this->_keepRatio = $options['keepRatio'];
        }
        if (isset($options['keepSmaller'])) {
            $this->_keepSmaller = $options['keepSmaller'];
        }
        if (isset($options['directory'])) {
            $this->_directory = $options['directory'];
        }
        if (isset($options['cropToFit'])) {
            $this->_cropToFit = $options['cropToFit'];
        }
        if (isset($options['follow'])) {
            $this->_follow = $options['follow'];
        }
        if (isset($options['jpegQuality']) && !is_numeric($options['jpegQuality'])) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Jpeg quality parameter must be numeric');
        } elseif ($options['jpegQuality'] < 0 || $options['jpegQuality'] > 100) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Jpeg quality parameter must be between 0 and 100');
        } elseif (isset($options['jpegQuality'])) {
            $this->_jpegQuality = $options['jpegQuality'];
        }
        if (isset($options['pngQuality']) && !is_numeric($options['pngQuality'])) {
            require_once 'Zend/Filter/Exception.php';
            throw new \Zend_Filter_Exception('Png quality parameter must be numeric');
        } elseif ($options['pngQuality'] < 0 || $options['pngQuality'] > 9) {
            require_once 'Zend/Filter/Exception.php';
            throw new \Zend_Filter_Exception('Png quality parameter must be between 0 and 9');
        } elseif (isset($options['pngQuality'])) {
            $this->_pngQuality = $options['pngQuality'];
        }
        if (isset($options['adapter'])) {
            if ($options['adapter'] instanceof Skoch_Filter_File_Resize_Adapter_Abstract) {
                $this->_adapter = $options['adapter'];
            } else {
                $name = $options['adapter'];
                if (substr($name, 0, 33) != 'Skoch_Filter_File_Resize_Adapter_') {
                    $name = 'Skoch_Filter_File_Resize_Adapter_' . ucfirst(strtolower($name));
                }
                $this->_adapter = $name;
            }
        }
        if (isset($options['png8Bits'])) {
            $this->_png8Bits = $options['png8Bits'];
        }
 
        $this->_prepareAdapter();
    }
    
    /**
     * Instantiate the adapter if it is not already an instance
     *
     * @return void
     */
    protected function _prepareAdapter()
    {
        if ($this->_adapter instanceof Skoch_Filter_File_Resize_Adapter_Abstract) {
            return;
        } else {
            $this->_adapter = new $this->_adapter();
        }
    }
 
    /**
     * Defined by Zend_Filter_Interface
     *
     * Resizes the file $value according to the defined settings
     *
     * @param  string $value Full path of file to change
     * @return string The filename which has been set, or false when there were errors
     */
    public function filter($value)
    {
        if ($this->_directory) {
            $target = $this->_directory . '/' . basename($value);
        } else {
            $target = $value;
        }
 
        $target = $this->_adapter->resize($this->_width, $this->_height,
            $this->_keepRatio, $value, $target, $this->_keepSmaller,
            $this->_cropToFit, $this->_jpegQuality, $this->_pngQuality, $this->_png8Bits);
        return $this->_follow ? $target : $value;
    }
}
