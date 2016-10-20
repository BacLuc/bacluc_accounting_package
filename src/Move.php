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
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityMultipleField;
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
 * Class MoveLine
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\MoveLine")
 * @Entity
@Table(name="bacluc_move"
)
 *
 */
class Move extends BaseEntity
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
    protected $name;

    /**
     * @Column(type="string")
     */
    protected $reference;

    /**
     * @Column(type="string")
     */
    protected $status;

    /**
     * @Column(type="date")
     */
    protected $date_posted;

    /**
     * @var MoveLine[]
     * @OneToMany(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\MoveLine", mappedBy="Move")
     */
    protected $MoveLines;



    const STATUS_DRAFT = 'draft';
    const STATUS_POSTED = 'posted';

    public function __construct(){
        parent::__construct();

        $this->setDefaultFieldTypes();
    }
    public function setDefaultFieldTypes(){
        parent::setDefaultFieldTypes();
        $this->fieldTypes['status']=new DropdownField('status', 'Status', 'poststatus');
        $refl = new \ReflectionClass($this);
        $constants = $refl->getConstants();
        $userConstants = array();
        foreach($constants as $key => $value){
            $userConstants[$value]=$value;
        }
        /**
         * @var DropdownField
         */
        $this->fieldTypes['status']->setOptions($userConstants);


        $MoveLines = $this->fieldTypes['MoveLines'];
        $directEditField = new DirectEditAssociatedEntityMultipleField($MoveLines->getSQLFieldName(), "Move Lines", $MoveLines->getPostName());
        DropdownLinkField::copyLinkInfo($MoveLines,$directEditField);
        $this->fieldTypes['MoveLines']=$directEditField;

    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction(){
        $function = function(Move $item){
            $returnString = '';
            return $returnString;
        };
        return $function;
    }

}