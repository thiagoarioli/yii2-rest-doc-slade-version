<?php

namespace thiagoarioli\restdoc\models;

use thiagoarioli\restdoc\helpers\DocBlockHelper;
use thiagoarioli\restdoc\helpers\DocBlockModel;
use phpDocumentor\Reflection\DocBlock;
use Yii;

/**
 * Parses Yii2 active record.
 */
class ModelParser extends ObjectParser
{
    /**
     * @param \thiagoarioli\restdoc\models\Doc
     * @return void
     */
    public function parse(Doc $doc)
    {
        $object = $this->getObject();
        foreach ($object->scenarios() as $key => $fields) {
            $doc->addScenario($key, $fields);
        }

        foreach ($object->extraFields() as $key => $value) {
            $doc->addExtraField(is_numeric($key) ? $value : $key);
        }

        foreach ($object->fields() as $key => $value) {
            $doc->addField(is_numeric($key) ? $value : $key);
        }

        $this->parseClass($doc);
        $this->parseFields($doc, 'fields');
        $this->parseFields($doc, 'extraFields');

        return true;
    }

    /**
     * @param $doc
     * @return bool
     */
    public function parseClass($doc)
    {
        if (!$docBlock = new DocBlockModel($this->reflection)) {
            return false;
        }

        $doc->populateProperties($docBlock);
        $doc->populateTagsModel($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseClass($doc);
        }
    }

    /**
     * @param \thiagoarioli\restdoc\models\ModelDoc $doc
     * @param string $methodName
     * @return bool
     */
    public function parseFields(ModelDoc $doc, $methodName)
    {
        if (!$docBlock = new DocBlock($this->reflection->getMethod($methodName))) {
            return false;
        }

        $doc->populateTags($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseFields($doc, $methodName);
        }
    }
}
