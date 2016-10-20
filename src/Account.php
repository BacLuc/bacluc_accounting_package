<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */
namespace Concrete\Package\BaclucAccountingPackage\Src;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField as DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FileField as FileField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\WysiwygField as WysiwygField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Expression;
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

/**
 * Class Account
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\Account")
 * @Entity
@Table(name="bacluc_account"
)
 *
 */
class Account extends BaseEntity
{
    use EntityGetterSetter;
    /**
     * @var int
     * @Id @Column(type="integer", nullable=false, options={"unsigned":true})
     * @GEneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $code;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string")
     */
    protected $type;

    /**
     * @Column(type="float")
     */
    protected $credit;

    /**
     * @Column(type="float")
     */
    protected $debit;

    /**
     * @Column(type="float")
     */
    protected $balance;

    const TYPE_OTHER = 'other';
    const TYPE_RECIEVABLE = 'recievable';
    const TYPE_PAYABLE = 'payable';
    const TYPE_liquidity = 'liquidity';

    public function __construct(){
        parent::__construct();

        $this->setDefaultFieldTypes();
    }
    public function setDefaultFieldTypes(){
        parent::setDefaultFieldTypes();
        $this->fieldTypes['type']=new DropdownField('type', 'Type', 'posttype');
        $refl = new \ReflectionClass($this);
        $constants = $refl->getConstants();
        $userConstants = array();
        foreach($constants as $key => $value){
            $userConstants[$value]=$value;
        }
        /**
         * @var DropdownField
         */
        $this->fieldTypes['type']->setOptions($userConstants);

    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction(){
        $function = function(Account $item){
            $returnString = '';
            return $returnString;
        };
        return $function;
    }

}