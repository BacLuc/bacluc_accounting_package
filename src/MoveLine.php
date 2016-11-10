<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */
namespace Concrete\Package\BaclucAccountingPackage\Src;
use Concrete\Flysystem\Exception;
use Concrete\Package\BaclucAccountingPackage\Src\EntityViews\MoveLineFormView;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\Exceptions\ConsistencyCheckException;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField as DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FileField as FileField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\WysiwygField as WysiwygField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Concrete\Package\BaclucPersonPackage\Src\Address;
use Concrete\Package\BaclucPersonPackage\Src\PostalAddress;


/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Doctrine\ORM\Mapping\Table;
use Concrete\Core\Package\Package;

/**
 * Class MoveLine
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\MoveLine")
 * @Entity
@Table(name="bacluc_move_line"
)
 *
 */
class MoveLine extends BaseEntity
{
    use EntityGetterSetter;
    /**
     * @var int
     * @Id @Column(type="integer", nullable=false, options={"unsigned":true})
     * @GEneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="float")
     */
    protected $debit;

    /**
     * @Column(type="float")
     */
    protected $credit;

    /**
     * @Column(type="float")
     */
    protected $balance;

    /**
     * @Column(type="boolean")
     */
    protected $reconciled;

    /**
     * @Column(type="date")
     */
    protected $date_posted;

    /**
     * @var Account
     * @ManyToOne(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\Account" ,inversedBy="MoveLines")
     */
    protected $Account;


    /**
     * @var Move
     * @ManyToOne(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\Move" ,inversedBy="MoveLines")
     */
    protected $Move;


    public function __construct(){
        parent::__construct();

        $this->setDefaultFieldTypes();
    }

    public function setDefaultFormViews()
    {
        $this->defaultFormView = new MoveLineFormView($this);
    }

    public function setDefaultFieldTypes(){
        parent::setDefaultFieldTypes();
        /**
         * @var Field[] $this->FieldTypes
         */
        $this->fieldTypes['Move']->setShowInForm(false);
        $this->fieldTypes['credit']->setMin(0);
        $this->fieldTypes['debit']->setMin(0);

    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction(){
        $function = function(MoveLine $item){
            $returnString = '';

            if(strlen($item->id) >0){

                $returnString.= $item->id." ";
            }

            if(is_object($item->Account)){
                $item->Account = BaseEntity::getBaseEntityFromProxy($item->Account);
                $accountDisplayStringFunction = Account::getDefaultGetDisplayStringFunction();
                $returnString.= $accountDisplayStringFunction($item->Account)." ";
            }
            $fieldTypes = $item->getFieldTypes();

            if(strlen($item->credit) >0){

                $returnString.= $fieldTypes['credit']->getLabel().": ".$item->credit." ";
            }
            if(strlen($item->debit) >0){

                $returnString.= $fieldTypes['debit']->getLabel().": ".$item->debit." ";
            }
            return $returnString;
        };
        return $function;
    }


    public function checkConsistency()
    {
        $errors = array();
        if($this->checkingConsistency){
            throw new ConsistencyCheckException();
        }
        $this->checkingConsistency = true;

        if(is_null($this->Account)){
            //$errors[]="Account can not be null";
        }else{
            try{
                $this->Account = static::getBaseEntityFromProxy($this->Account);
                $accountErrors = $this->Account->checkConsistency();
                foreach($accountErrors as $key => $value){
                    $errors[]=$value;
                }
            }catch (ConsistencyCheckException $e){

            }
        }

        if(is_null($this->Move)){
            //$errors[]="Move can not be null";
        }else{
            try{
                $this->Move = static::getBaseEntityFromProxy($this->Move);
                $moveErrors = $this->Move->checkConsistency();
                foreach($moveErrors as $key => $value){
                    $errors[]=$value;
                }
            }catch (ConsistencyCheckException $e){

            }
        }

        $this->balance = $this->debit - $this->credit;




        $this->getEntityManager()->persist($this);
        $this->checkingConsistency = false;
        return $errors;
    }

    public function set($name, $value)
    {
        if($name == "Account" && $this->Account !=null){
            $this->Account->checkConsistency();
        }
        if($name == "Move" && $this->Move != null){

        }
        return parent::set($name,$value);
    }

}