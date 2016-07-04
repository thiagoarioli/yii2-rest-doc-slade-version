<?php

namespace thiagoarioli\restdoc\models;

use thiagoarioli\restdoc\helpers\DocBlockClass;
use thiagoarioli\restdoc\helpers\DocBlockHelper;
use thiagoarioli\restdoc\helpers\DocBlockMethod;
use phpDocumentor\Reflection\DocBlock;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;



class ControllerParser extends ObjectParser
{
    /**
     * @var string[] List of controller's actions.
     */
    public $actions = [];

    /**
     * @var \thiagoarioli\restdoc\models\ModelDoc
     */
    public $model;

    /**
     * @var array Controller constructor's params
     */
    public $objectArgs = [null, null];

    /**
     * @var string Path to controllers (part of url).
     */
    public $path;

    /**
     * @var array of query tags
     */
    public $query;


    /** @var string The opening line for this docblock. */
    protected $short_description = '';

    /**
     * @var DocBlock\Description The actual
     *     description for this docblock.
     */
    protected $long_description = null;

    /**
     * @var Tag[] An array containing all
     *     the tags in this docblock; except inline.
     */
    protected $tags = array();

    /** @var Context Information about the context of this DocBlock. */
    protected $context = null;

    /** @var Location Information about the location of this DocBlock. */
    protected $location = null;

    /** @var bool Is this DocBlock (the start of) a template? */
    protected $isTemplateStart = false;

    /** @var bool Does this DocBlock signify the end of a DocBlock template? */
    protected $isTemplateEnd = false;
    /**
     * @param \thiagoarioli\restdoc\models\Doc
     * @return void
     */
    public function parse(Doc $doc)
    {
        if ($this->reflection->isAbstract()) {
            $this->error = $this->reflection->name . " is abstract";
            return false;
        }

        $this->parseClass($doc);

        if ($doc->getTagsByName('ignore')) {
            $this->error = $this->reflection->name . " has ignore tag";
            return false;
        }

        $object = $this->getObject();

        $doc->path = Inflector::camel2id(substr($this->reflection->getShortName(), 0, -strlen('Controller')));
        $config = ArrayHelper::toArray(
            require(Yii::getAlias('@api').'/config/main.php')
        );

        $config = ArrayHelper::merge(
            $object->actions(),
            $config['components']['urlManager']['rules']
        );


        $doc->actions = $this->parseMethod($doc,$config,$doc->path);
        unset($doc->actions['options']);
        // Parse model
        $modelParser = Yii::createObject(
            [
                'class' => '\thiagoarioli\restdoc\models\ModelParser',
                'reflection' => new \ReflectionClass($this->getObject()->modelClass),
            ]
        );
        $doc->model = new ModelDoc();
        $modelParser->parse($doc->model);

        return true;
    }

    /**
     * @param $doc
     * @return bool
     */
    public function parseMethod(ControllerDoc $doc,$actions,$path)
    {
        $var = [];
        foreach($actions as $key => $value){
            try{
                if(!isset($value['extraPatterns'])){
                    switch ($key){
                        case "view":
                        case "index":
                            $var[$key]['request'] = ['GET'];
                            break;
                        case "create":
                            $var[$key]['request'] = ['POST'];
                            break;
                        case "update":
                            $var[$key]['request']= ['PUT'];
                            break;
                        case "delete":
                            $var[$key]['request'] = ['DELETE'];
                            break;
                        case "options":
                            $var[$key]['request'] = ['OPTIONS'];
                            break;
                        default:
                            $var[$key]['request'] = ['GET'];
                    }
                    continue;
                }
                foreach($value['extraPatterns'] as $keyy => $valuee){

                    if(explode('/',$value['controller'][0])[1] != $path){
                        continue;
                    }
                    if (!$docBlock = new DocBlockMethod($this->reflection,$this->getAction($valuee))) {
                        return false;
                    }
                    $doc->populateTagsMethod($docBlock);

                    $var[$valuee]['short'] = $docBlock->getShortDescription();
                    $var[$valuee]['request'] = [explode(' ', $keyy)[0]];
                    $var[$valuee]['tags'] = $docBlock->getTags();
                }

            }catch (\ReflectionException $e){
                $value['request'] = ['HUE'];
            }
        }
        return $var;
    }


    public function getAction($id){
        return $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
    }

    /**
     * @param $doc
     * @return bool
     */
    public function parseClass(ControllerDoc $doc)
    {
        if (!$docBlock = new DocBlock($this->reflection)) {
            return false;
        }

        $doc->longDescription = $docBlock->getLongDescription()->getContents();
        $doc->shortDescription = $docBlock->getShortDescription();

        $doc->populateTags($docBlock);

        if (DocBlockHelper::isInherit($docBlock)) {
            $parentParser = $this->getParentParser();
            $parentParser->parseClass($doc);
        }
    }
}
