<?php
namespace Concrete\Package\BaclucAccountingPackage\Src\BlockOptions;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Doctrine\ORM\Mapping\Table;
use Concrete\Package\BasicTablePackage\Src\Group as Group;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
/**
 * Class GroupRefOption
 * @IgnoreAnnotation("package")
 * @IgnoreAnnotation("package")\n*  Concrete\Package\BasicTablePackage\Src\BlockOptions
 * @Entity
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\BlockOptions\AccountRefOption")
 */
class AccountRefOption extends TableBlockOption{
    use EntityGetterSetter;
    /**
     * @var string
     * @Column(type="string")
     */
    protected $optionType =__CLASS__;
    /**
     * @var ArrayCollection of Group
     * @OneToMany(targetEntity="Concrete\Package\BasicAccountingPackage\Src\Account")

     */
    protected $AccountAssociations;
    public function __construct()
    {
        $this->AccountAssociations = new ArrayCollection();
        $this->optionType == __CLASS__;
        $this->setDefaultFieldTypes();
    }
    public function getLabel(){
        return t('Which Accounts are used in this Block?');
    }
    public function getFieldType(){
        if($this->fieldTypes['optionValue']==null){
            $this->setDefaultFieldTypes();
        }
        if($this->optionName != null){
            $this->fieldTypes['AccountAssociations']->setLabel($this->optionName);
            $this->fieldTypes['AccountAssociations']->setPostName(str_replace(" ", "", $this->optionName));
        }
        return $this->fieldTypes['AccountAssociations'];
    }
    public function getValue(){
        if($this->AccountAssociations instanceof PersistentCollection){
            $this->AccountAssociations = new ArrayCollection($this->AccountAssociations->toArray());
        }
        return $this->AccountAssociations;
    }
    public function setValue($AccountAssociations){
        if($AccountAssociations instanceof PersistentCollection){
            $AccountAssociations = new ArrayCollection($AccountAssociations->toArray());
        }
        if($AccountAssociations instanceof  Entity){

            $idfieldname =$AccountAssociations->getIdFieldName();
            $optionValue = $this->getEntityManager()
                ->getRepository($AccountAssociations::getFullClassName())
                ->findOne(array(
                    $AccountAssociations->getIdFieldName() => $AccountAssociations->$idfieldname
                ));
        }elseif($AccountAssociations instanceof ArrayCollection){

            foreach($this->AccountAssociations->toArray() as $key => $value){
                $this->AccountAssociations->removeElement($value);
                $this->getEntityManager()->remove($value);
            }
            foreach($AccountAssociations->toArray() as $key => $value){
                $idfieldname =$value->getIdFieldName();
                $this->getEntityManager()->persist($value);
                $this->AccountAssociations->add($value);
            }

        }



    }
}