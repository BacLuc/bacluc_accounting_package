<?php

namespace Concrete\Package\BaclucAccountingPackage\Block\BaclucAccountBlock;

use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\CanEditOption;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;

class Controller extends \Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Controller
{
    protected $btHandle = 'bacluc_account_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucAccountBlock";

    /**
     * Array of \Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * @var \Concrete\Package\BasicTablePackage\Src\BaseEntity
     */
    protected $model;


    /**
     * set blocktypeset
     * @var string
     */
    protected $btDefaultSet = 'bacluc_accounting_set';

    /**
     *
     * Controller constructor.
     * @param null $obj
     */
    function __construct ($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new Account();
        parent::__construct($obj);


        if ($obj instanceof Block) {
            $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')
                       ->findOneBy(array( 'bID' => $obj->getBlockID() ))
            ;

            $this->basicTableInstance = $bt;
        }


        /*
         * add blockoptions here if you wish
                $this->requiredOptions = array(
                    new TextBlockOption(),
                    new DropdownBlockOption(),
                    new CanEditOption()
                );

                $this->requiredOptions[0]->set('optionName', "Test");
                $this->requiredOptions[1]->set('optionName', "TestDropDown");
                $this->requiredOptions[1]->setPossibleValues(array(
                    "test",
                    "test2"
                ));

                $this->requiredOptions[2]->set('optionName', "testlink");
        */


    }


    /**
     * @return string
     */
    public function getBlockTypeDescription ()
    {
        return t("Create, Edit or Delete Accounts");
    }

    /**
     * @return string
     */
    public function getBlockTypeName ()
    {
        return t("Bacluc Account");
    }


}
