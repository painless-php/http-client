<?php

namespace PainlessPHP\Http\Client\Parser;

use DOMNode;
use PainlessPHP\Http\Client\Contract\BodyParser;
use PainlessPHP\Http\Client\Exception\BodyParsingException;
use PainlessPHP\Http\Message\Body;

class XmlBodyParser implements BodyParser
{
    public function parseBody(Body $body) : array
    {
        // Enable user error handling to catch exceptions and display error messages
        $previousSetting = libxml_use_internal_errors(true);
        $result = simplexml_load_string($body->getContents(), "SimpleXMLElement", LIBXML_NOCDATA);

        if($result === false) {

            $errors = libxml_get_errors();

            if(empty($errors)) {
                throw new BodyParsingException('Syntax error');
            }

            $messages = implode(PHP_EOL, array_map(function($error) {
                $message = trim($error->message);
                return "Could not parse line $error->line, column $error->column, $message";
            }, $errors));

            throw new BodyParsingException($messages);
        }

        $result = dom_import_simplexml($result);
        $result = [$result->tagName => $this->domnodeToArray($result)];

        // Revert error handling setting to its previous state if it was changed
        if(! $previousSetting) {
            libxml_use_internal_errors($previousSetting);
        }

        return $result;
    }

    private function domnodeToArray(DOMNode $node) : array|string
    {
        $output = [];

        switch ($node->nodeType) {
        case XML_CDATA_SECTION_NODE:
        case XML_TEXT_NODE:
            $output = trim($node->textContent);
            break;
        case XML_ELEMENT_NODE:

            // Add child nodes to output
            for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {

                $child = $node->childNodes->item($i);
                $v = $this->domnodeToArray($child);
                $t = $child->tagName ?? null;

                if($t !== null) {
                    if(!isset($output[$t])) {
                        if(! is_array($output)) continue;
                        $output[$t] = [];
                    }
                    $output[$t][] = $v;
                }
                elseif($v || $v === '0') {
                    $output = (string) $v;
                }
            }

            if($node->attributes->length && ! is_array($output)) {
                // Has attributes but isn't an array
                // Change output into an array.
                $output = ['@content' => $output];
            }

            if(is_array($output)) {
                if($node->attributes->length) {
                    $a = [];
                    foreach($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = (string) $attrNode->value;
                    }
                    $output['@attributes'] = $a;
                }
                foreach ($output as $t => $v) {
                    if(is_array($v) && count($v)==1 && $t!='@attributes') {
                        $output[$t] = $v[0];
                    }
                }
            }
            break;
        }
        return $output;
    }
}
