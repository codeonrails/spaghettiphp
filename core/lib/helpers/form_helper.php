<?php
/**
 *  Geração automática do formulário em HTML de acordo com os dados passados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

App::import("Helper", "html_helper");

class FormHelper extends HtmlHelper {
    /**
     *  Retorna um elemento HTML do formulário formatado.
     * 
     *  @param string $action Ação atual do modelo
     *  @param array $options Atributos e opções da tag HTML
     *  @return string Tag FORM aberto e formatado
     */
    public function create($action = null, $options = array()) {
        $attributes = array_merge(
            array(
                "method" => "post",
                "action" => Mapper::url($action)
            ),
            $options
        );
        if($attributes["method"] == "file"):
            $attributes["method"] = "post";
            $attributes["enctype"] = "multipart/form-data";
        endif;
        return $this->output($this->openTag("form", $attributes));
    }
    /**
     *  Fecha um elemento HTML do formulário de acordo com os atributos repassados.
     *
     *  @param boolean $submit Botão e envio do formulário
     *  @param array $attributes Atributos e opções da tag HTML
     *  @return string Tag FORM fechada
     */
    public function close($submit = null, $attributes = array()) {
        $form = $this->closeTag("form");
        if(!is_null($submit)):
            $form = $this->submit($submit, $attributes) . $form;
        endif;
        return $this->output($form);
    }
    /**
     *  Cria um botão de envio dos dados do formulário.
     *
     *  @param string $submit Nome do botão de envio
     *  @param array $attributes Atributos e opções da tag
     *  @return string Botão de envio do formulário
     */
    public function submit($text, $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "submit",
                "tag" => "button"
            ),
            $attributes
        );
        if(array_unset($attributes, "tag") == "input"):
            $attributes["value"] = $text;
            $button = $this->tag("input", null, $attributes, false);
        else:
            $button = $this->tag("button", $text, $attributes);
        endif;
        return $this->output($button);
    }
    /**
     *  Cria uma caixa de seleção.
     * 
     *  @param string $name Nome da caixa de seleção
     *  @param array $options Atributos da tag
     *  @return string Caixa de seleção do formulário
     */
    public function select($name, $options = array()) {
        $options = array_merge(array(
            "name" => $name,
            "options" => array(),
            "value" => null,
            "empty" => false
        ), $options);
        $selectOptions = array_unset($options, "options");
        if(($empty = array_unset($options, "empty")) !== false):
            $keys = array_keys($selectOptions);
            array_unshift($keys, $empty);
            $values = array_merge(array(""), $selectOptions);
            $selectOptions = array_combine($keys, $values);
        endif;
        $content = "";
        foreach($selectOptions as $key => $value):
            $optionAttr = array("value" => $key);
            if((string) $key === (string) $options["value"]):
                $optionAttr["selected"] = true;
                unset($options["value"]);
            endif;
            $content .= $this->tag("option", $value, $optionAttr);
        endforeach;
        return $this->output($this->tag("select", $content, $options));
    }
    /**
     *  Cria um input radio.
     *
     *  @param string $name Nome do input
     *  @param array $options Atributos da tag
     *  @return string Input do formulário
     */
    public function radio($name, $options = array()) {
        $options = array_merge(array(
            "options" => array(),
            "value" => null
        ), $options);
        $content = "";
        $radioOptions = array_unset($options, "options");
        foreach($radioOptions as $key => $value):
            $radioAttr = array(
                "type" => "radio",
                "value" => $key,
                "id" => Inflector::camelize("{$name}_{$key}"),
                "name" => $name,
            );
            if($key === $options["value"]):
                $radioAttr["checked"] = true;
                unset($options["value"]);
            endif;
            $content .= $this->tag("input", null, $radioAttr, false);
            $content .= $this->tag("label", $value, array("for" => $radioAttr["id"]));
        endforeach;
        return $this->output($content);
    }
    /**
     *  Cria caixa de entrada formatada e com label.
     * 
     *  @param string $name Nome do campo de entrada
     *  @param array $options Atributos da tag
     *  @return string Campo de entrada do formulário
     */
    public function input($name, $options = array()) {
        $options = array_merge(array(
            "name" => $name,
            "type" => "text",
            "id" => Inflector::camelize("form_" . Inflector::slug($name)),
            "label" => Inflector::humanize($name),
            "div" => true
        ), $options);
        $label = array_unset($options, "label");
        $div = array_unset($options, "div");
        switch($options["type"]):
            case "select":
                $selectOptions = $options;
                unset($selectOptions["type"]);
                $input = $this->select($name, $selectOptions);
                break;
            case "radio":
                $label = false;
                $input = $this->radio($name, $options);
                break;
            case "date":
                $input = $this->date($name, $options);
                break;
            default:
                if($options["type"] == "hidden"):
                    $div = $label = false;
                elseif($name == "password"):
                    $options["type"] = "password";
                endif;
                $input = $this->tag("input", null, $options, false);
        endswitch;
        if($label):
            $input = $this->tag("label", $label, array("for" => $options["id"])) . $input;
        endif;
        
        if($div):
            if($div === true):
                $div = "input {$options['type']}";
            endif;
            $input = $this->div($input, $div);
        endif;
        return $this->output($input);
    }
    /**
     *  Cria um conjunto de caixa de seleção para a data.
     * 
     *  @param string $name Nome do conjunto de caixas de seleção
     *  @param array $options Opções das caixas de seleção
     *  @return string Conjunto de caixa de seleção
     */
    public function date($name, $options = array()) {
        $options = array_merge(array(
            "startYear" => 1980,
            "endYear" => date("Y"),
            "currentDay" => date("d"),
            "currentMonth" => date("m"),
            "currentYear" => date("Y")
        ), $options);
        $days = array_range(1, 31);
        $months = array_range(1, 12);
        $years = array_range($options["startYear"], $options["endYear"]);
        $selectDay = $this->select($name . "[d]", array("value" => $options["currentDay"], "options" => $days));
        $selectMonth = $this->select($name . "[m]", array("value" => $options["currentMonth"], "options" => $months));
        $selectYear = $this->select($name . "[y]", array("value" => $options["currentYear"], "options" => $years));
        return $selectDay . $selectMonth . $selectYear;
    }
}

?>