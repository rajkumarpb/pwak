    // class properties {{{
[[foreach item=property from=$entity.properties]]

    /**
     * _[[$entity.name]]::[[$property.name]]
     * [[$property.name]] [[$property.type.name]] property
     *
     * @access private
     * @var [[$property.type.name]]
     */
    protected $[[$property.name]] = [[$property.default]];
[[/foreach]]

    // }}}
[[* class properties accessors {{{ *]]
[[foreach item=property from=$entity.properties]]
[[if in_array($property.type.const, array('TYPE_INT', 'TYPE_BOOL'))]]
[[* TYPE_INT, TYPE_BOOL {{{ *]]
    // _[[$entity.name]]::set[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::set[[$property.name]]
     *
     * @access public
     * @param [[$property.type.name]] $value
     * @return void
     */
    public function set[[$property.name]]($value) {
        if ($value !== null) {
            $this->[[$property.name]] = (int)$value;
        }
    }

    // }}}
[[* }}} *]]
[[elseif in_array($property.type.const, array('TYPE_FLOAT', 'TYPE_DECIMAL'))]]
[[* TYPE_FLOAT, TYPE_DECIMAL {{{ *]]
    // _[[$entity.name]]::set[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::set[[$property.name]]
     *
     * @access public
     * @param [[$property.type.name]] $value
     * @return void
     */
    public function set[[$property.name]]($value) {
        if ($value !== null) {
            $this->[[$property.name]] = I18N::extractNumber($value);
        }
    }

    // }}}
[[* }}} *]]
[[elseif $property.type.const eq 'TYPE_CONST']]
[[* TYPE_CONST       {{{ *]]
    // _[[$entity.name]]::set[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::set[[$property.name]]
     *
     * @access public
     * @param [[$property.type.name]] $value
     * @return void
     */
    public function set[[$property.name]]($value) {
        if ($value !== null) {
            $this->[[$property.name]] = (int)$value;
        }
    }

    // }}}
    // _[[$entity.name]]::get[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::get[[$property.name]]ConstArray
     * Return an array with the property constants and their textual
     * representation.
     *
     * @access public
     * @static
     * @param boolean $keys true to get only the constants
     * @return array
     */
    public static function get[[$property.name]]ConstArray($keys = false) {
        $array = array(
[[foreach name=constarray item=const from=$property.constarray]]
            _[[$entity.name]]::[[$const.name]] => _('[[$const.label]]')[[if not $smarty.foreach.constarray.last]],
[[/if]]
[[/foreach]]
        );
        asort($array);
        return $keys?array_keys($array):$array;
    }

    // }}}
[[* }}} *]]
[[elseif in_array($property.type.const, array('TYPE_DATETIME', 'TYPE_DATE', 'TYPE_TIME'))]]
[[* TYPE_DATETIME, TYPE_DATE, TYPE_TIME {{{ *]]
    // _[[$entity.name]]::get[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::get[[$property.name]]
     * Return the property formated used $format, if $format is unset the
     * property is returned in the database format.
     *
     * The acceptable values for $format are describe in Object::dateFormat().
     *
     * @access public
     * @param string format see Object::dateFormat() to details
     * @return mixed
     */
    public function get[[$property.name]]($format = false) {
        return $this->dateFormat($this->[[$property.name]], $format);
    }

    // }}}
[[* }}} *]]
[[elseif in_array($property.type.const, array('TYPE_I18N_STRING', 'TYPE_I18N_TEXT', 'TYPE_I18N_HTML'))]]
[[* TYPE_I18N_STRING, TYPE_I18N_TEXT, TYPE_I18N_HTML {{{ *]]
    // _[[$entity.name]]::get[[$property.name]]() {{{
  
    /**
     * _[[$entity.name]]::get[[$property.name]]
     *
     * @access public
     * @param string $locale optional, default is the current locale code
     * @param boolean $useDefaultLocaleIfEmpty determine if the getter must
     * return the translation in the DEFAULT_LOCALE if no translation is found
     * in the current locale.
     * @return string
     */
    public function get[[$property.name]]($locale=false, $defaultLocaleIfEmpty=true) {
        $locale = $locale !== false ? $locale : I18N::getLocaleCode();
        if (is_int($this->[[$property.name]]) && $this->[[$property.name]] > 0) {
            $this->[[$property.name]] = Object::load('I18nString', $this->[[$property.name]]);
        }
        $ret = null;
        if ($this->[[$property.name]] instanceof I18nString) {
            $getter = 'getStringValue_' . $locale;
            $ret = $this->[[$property.name]]->$getter();
            if ($ret == null && $defaultLocaleIfEmpty) {
                $getter = 'getStringValue_' . LOCALE_DEFAULT;
                $ret = $this->[[$property.name]]->$getter();
            }
        }
        return $ret;
    }
  
    // }}}
    // _[[$entity.name]]::get[[$property.name]]Id() {{{

    /**
     * _[[$entity.name]]::get[[$property.name]]Id
     *
     * @access public
     * @return integer
     */
    public function get[[$property.name]]Id() {
        if ($this->[[$property.name]] instanceof I18nString) {
            return $this->[[$property.name]]->getId();
        }
        return (int)$this->[[$property.name]];
    }

    // }}}
    // _[[$entity.name]]::set[[$property.name]]() {{{

    /**
     * _[[$entity.name]]::set[[$property.name]]
     *
     * @access public
     * @param string $value
     * @param string $locale optional, default is the current locale code
     * @return void
     */
    public function set[[$property.name]]($value, $locale=false) {
        if (is_numeric($value)) {
            $this->[[$property.name]] = (int)$value;
        } else if ($value instanceof I18nString) {
            $this->[[$property.name]] = $value;
        } else {
            $locale = $locale !== false ? $locale : I18N::getLocaleCode();
            if (!($this->[[$property.name]] instanceof I18nString)) {
                $this->[[$property.name]] = Object::load('I18nString', $this->[[$property.name]]);
                if (!($this->[[$property.name]] instanceof I18nString)) {
                    $this->[[$property.name]] = new I18nString();
                }
            }
            $setter = 'setStringValue_'.$locale;
            $this->[[$property.name]]->$setter($value);
            $this->[[$property.name]]->save();
        }
    }

    // }}}
[[* }}} *]]
[[/if]]
[[/foreach]]
[[* }}} * ]]
    // ONLY FOR COMPATIBILITY {{{
[[foreach item=property from=$entity.properties]]
[[if in_array($property.type.const, array('TYPE_INT', 'TYPE_BOOL', 'TYPE_FLOAT', 'TYPE_DECIMAL', 'TYPE_CONST'))]]

    /**
     * _[[$entity.name]]::get[[$property.type.name]]()
     *
     * @access public
     * @return [[$property.type.name]]
     */
    public function get[[$property.name]]() {
        return $this->[[$property.name]];
    }
[[elseif in_array($property.type.const, array('TYPE_DATETIME', 'TYPE_DATE', 'TYPE_TIME'))]]

    /**
     * _[[$entity.name]]::set[[$property.type.name]]()
     *
     * @param [[$property.type.name]] $value
     * @access public
     * @return bool
     */
    public function set[[$property.name]]($value) {
        return $this->[[$property.name]] = $value;
    }
[[elseif !in_array($property.type.const, array('TYPE_I18N_STRING', 'TYPE_I18N_TEXT', 'TYPE_I18N_HTML'))]]
    
    /**
     * _[[$entity.name]]::get[[$property.type.name]]()
     *
     * @access public
     * @return [[$property.type.name]]
     */
    public function get[[$property.name]]() {
        return $this->[[$property.name]];
    }

    /**
     * _[[$entity.name]]::set[[$property.type.name]]()
     *
     * @param [[$property.type.name]] $value
     * @access public
     * @return bool
     */
    public function set[[$property.name]]($value) {
        return $this->[[$property.name]] = $value;
    }
[[/if]]
[[/foreach]]
    // }}}

