<?php

namespace Wyra\Kernel\View;

use Smarty;
use Wyra\Kernel\Crypt;
use Wyra\Kernel\Kernel;

/**
 * SmartyExtender of WyRa
 *
 * Copyright (c) 2017, Raffael Wyss <raffael.wyss@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Raffael Wyss nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @autor       Raffael Wyss <raffael.wyss@gmail.com>
 * @copyright   2017 Raffael Wyss. All rights reserved.
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ViewSMARTYExtender
{

    /** @var array  */
    private $arguments = array();

    private $templatePath = '';

    /** @var null|Smarty  */
    private $smarty = null;


    /**
     * Registriert alle notwendigen Plugins
     *
     * @param Smarty $smarty
     *
     * @return mixed
     */
    public function registerPlugins($smarty, $args = array())
    {
        $this->arguments = $args;
        $this->templatePath = Kernel::$config->get('rootPath').'/Kernel/View/Smarty/templates';
        $this->smarty = $smarty;
        $smarty->registerPlugin('function', '_', [$this, 'underline']);
        $smarty->registerPlugin('function', "input", [$this, 'input']);
        $smarty->registerPlugin('function', "formdata", [$this, 'formData']);
        $smarty->registerPlugin('function', "formelement", [$this, 'formElement']);
        $smarty->registerPlugin('function', 'formelements', [$this, 'formElements']);
        return $smarty;
    }

    /**
     * Hier kommen alle Underline-Plugins
     *
     * @param array $params
     *
     * @return mixed|null
     */
    public function underline($params)
    {
        // Icon
        if (isset($params['I'])) {
            return $this->underlineIcon($params['I']);
        }

        // Sprache
        if (isset($params['L'])) {
            $paramsNew = $params;
            unset($paramsNew['L']);
            return $this->underlineLanguage($params['L'], $paramsNew);
        }

        // Url
        if (isset($params['U'])) {
            return $this->underlineURL($params['U']);
        }
    }

    public function formData($params)
    {
        $type = (isset($params['type'])) ? $params['type'] : '';

        $formDataString = "form_data=";

        $elements = $this->smarty->getTemplateVars('elements');

        if ($type === 'group') {
            $elementsmerge = array();
            foreach ($elements['elements'] AS $elements) {
                foreach ($elements AS $element) {
                    $elementsmerge[$element['name']] = (isset($element['value'])) ? $element['value'] : '';
                }

            }
            $elements = $elementsmerge;
        } else {
            $elementsmerge = array();
            foreach ($elements AS $element) {
                $elementsmerge[$element['name']] = (isset($element['value'])) ? $element['value'] : '';
            }
            $elements = $elementsmerge;
        }

        $formatDataString = '<script type="text/javascript">';
        $formatDataString .= "formdata=".json_encode($elements)."";
        $formatDataString .= '</script>';

        return $formatDataString;

    }

    public function formElements($params)
    {
        // Übernahme der Parameter
        $group = (isset($params['group'])) ? $params['group'] : '';


        // Element auslesen
        $elements = $this->smarty->getTemplateVars('elements');
        if ($group != '' and isset($elements['elements'][$group]) and is_array($elements['elements'][$group])) {
            $elements = $elements['elements'][$group];
        }

        $elementsString = '';
        foreach ($elements AS $element) {
            switch($element['element']) {
                case 'input':
                    $elementsString .= $this->input($element);
                    break;
                default:
                    throw new \RuntimeException(Kernel::$language->getText('UNBEKANNTESELEMENT'));
                    break;
            }
        }
        return $elementsString;


    }

    public function formElement($params)
    {
        // Übernahme der Parameter
        $name = (isset($params['name'])) ? $params['name'] : '';
        $group = (isset($params['group'])) ? $params['group'] : '';

        // Element auslesen
        $elements = $this->smarty->getTemplateVars('elements');
        if (isset($elements['elements'][$group]) AND is_array($elements['elements'][$group])) {
            $elements = $elements['elements'][$group];
        }

        $elementsString = '';
        foreach ($elements AS $element) {
            if (isset($element['element']) AND $element['name'] === $name) {
                switch($element['element']) {
                    case 'input':
                        $elementsString .= $this->input($element);
                        break;
                    default:
                        throw new \RuntimeException(Kernel::$language->get('UNBEKANNTESELEMENT'));
                        break;
                }
            }
        }
        return $elementsString;
    }


    public function input($params)
    {
        $type = (isset($params['type'])) ? $params['type'] : 'text';
        switch ($type) {
            case 'text':
                return $this->inputInput($params, $type);
                break;
            case 'password':
                return $this->inputInput($params, $type);
                break;
            default:
                break;
        }

    }

    private function inputInput($params, $type)
    {
        // Übernahme der Parameter
        $label = (isset($params['label'])) ? $this->underlineLanguage($params['label']) : '';
        $name = (isset($params['name'])) ? $params['name'] : '';
        $id = (isset($params['id'])) ? $params['id'] : $name;
        $value = (isset($params['value'])) ? $params['value'] : '';
        $placeholder = (isset($params['placeholder'])) ? $params['placeholder'] : $label;
        $required = (isset($params['required'])) ? true : false;
        $disabled = (isset($params['disabled'])) ? true : false;
        $readonly = (isset($params['readonly'])) ? true : false;

        // Rückgabe
        $this->smarty->assign('type', $type);
        $this->smarty->assign('label', $label);
        $this->smarty->assign('id', $id);
        $this->smarty->assign('name', $name);
        $this->smarty->assign('value', $value);
        $this->smarty->assign('placeholder', $placeholder);
        $this->smarty->assign('required', $required);
        $this->smarty->assign('disabled', (!empty($disabled)) ? $disabled.' || form.busy' : 'form.busy' );
        $this->smarty->assign('readonly', $readonly);
        return $this->smarty->fetch($this->templatePath.'/input.tpl');
    }



    /**
     * Abfrage der Sprache
     *
     * @param string $text
     *
     * @return mixed|null
     */
    private function underlineLanguage($text, $params = array())
    {
        return Kernel::$language->getText($text, $params);
    }

    /**
     * Abfrage des Icons
     *
     * @param string $icon
     *
     * @return string
     */
    private function underlineIcon($icon)
    {
        $icons = explode('_', $icon);
        if (count($icons) === 0) {
            return '';
        }
        $returnIcon = 'icon ';
        foreach ($icons AS $icon) {
            $returnIcon .= 'icon-'.$icon.' ';
        }
        return '<i class="'.$returnIcon.'"></i>';
    }

    /**
     * Baut die URL aus dem Controller auf
     *
     * @param $url
     *
     * @return string
     */
    private function underlineURL($url)
    {
        if (isset($this->arguments['urllist'][$url])) {
            $url = $this->arguments['urllist'][$url];
        }
        if (Kernel::$config->get('routeCrypt')) {
            $crypt = new Crypt();
            return $crypt->crypt($url);
        } else {
            return $url;
        }
    }




}