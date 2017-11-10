<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="medicines", options={"comment":"Contiene los Medicamentos"})
 */
class Medicine 
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="cum_code", options={"comment":"codigo CUM + Consec en 2 digitos"})
     */
    private $cumCode;  

    /**
     * @ORM\Column(type="string", name="product", options={"comment":"Nombre del producto"})
     */
    private $product;  

    /**
     * @ORM\Column(type="string", name="laboratory", options={"comment":"Titular del medicamento"})
     */
    private $laboratory; 

    /**
     * @ORM\Column(type="text", name="comercial_descripcion", options={"comment":"Descripcion comercial del medicamento"})
     */
    private $comercialDescripcion;  

    /**
     * @ORM\Column(type="string", length=60, name="status_cum", options={"comment":"Estado del CUM"})
     */
    private $statusCum;  

    /**
     * @ORM\Column(type="string", length=60, name="unity", options={"comment":"Unidad de medida del medicamento"})
     */
    private $unity;  

    /**
     * @ORM\Column(type="string", name="atc_code", options={"comment":"Codigo ATC"})
     */
    private $atcCode;  

    /**
     * @ORM\Column(type="string", name="atc_description", options={"comment":"Descripcion ATC"})
     */
    private $atcDescription;   

     /**
     * @ORM\Column(type="string", length=60, name="route_administration", options={"comment":"via de administración del medicamento"})
     */
    private $routeAdministration;  

    /**
     * @ORM\Column(type="string", length=10,name="concentration", options={"comment":"Concentracion"})
     */
    private $concentration;  

    /**
     * @ORM\Column(type="text", name="active_principle", options={"comment":"Principio Activo"})
     */
    private $activePrinciple; 

    /**
     * @ORM\Column(type="string", name="unity_measure", options={"comment":"Unidad medida"})
     */
    private $unityMeasure;

    /**
     * @ORM\Column(type="string", name="reference_unit", options={"comment":"unidad de referencia"})
     */
    private $referenceUnit; 

     /**
     * @ORM\Column(type="string", name="pharmaceutical_form", options={"comment":"Forma farmaceutica"})
     */
    private $pharmaceuticalForm;        


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cumCode
     *
     * @param string $cumCode
     *
     * @return Medicine
     */
    public function setCumCode($cumCode)
    {
        $this->cumCode = $cumCode;

        return $this;
    }

    /**
     * Get cumCode
     *
     * @return string
     */
    public function getCumCode()
    {
        return $this->cumCode;
    }

    /**
     * Set product
     *
     * @param string $product
     *
     * @return Medicine
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set laboratory
     *
     * @param string $laboratory
     *
     * @return Medicine
     */
    public function setLaboratory($laboratory)
    {
        $this->laboratory = $laboratory;

        return $this;
    }

    /**
     * Get laboratory
     *
     * @return string
     */
    public function getLaboratory()
    {
        return $this->laboratory;
    }

    /**
     * Set comercialDescripcion
     *
     * @param string $comercialDescripcion
     *
     * @return Medicine
     */
    public function setComercialDescripcion($comercialDescripcion)
    {
        $this->comercialDescripcion = $comercialDescripcion;

        return $this;
    }

    /**
     * Get comercialDescripcion
     *
     * @return string
     */
    public function getComercialDescripcion()
    {
        return $this->comercialDescripcion;
    }

    /**
     * Set statusCum
     *
     * @param string $statusCum
     *
     * @return Medicine
     */
    public function setStatusCum($statusCum)
    {
        $this->statusCum = $statusCum;

        return $this;
    }

    /**
     * Get statusCum
     *
     * @return string
     */
    public function getStatusCum()
    {
        return $this->statusCum;
    }

    /**
     * Set unity
     *
     * @param string $unity
     *
     * @return Medicine
     */
    public function setUnity($unity)
    {
        $this->unity = $unity;

        return $this;
    }

    /**
     * Get unity
     *
     * @return string
     */
    public function getUnity()
    {
        return $this->unity;
    }

    /**
     * Set atcCode
     *
     * @param string $atcCode
     *
     * @return Medicine
     */
    public function setAtcCode($atcCode)
    {
        $this->atcCode = $atcCode;

        return $this;
    }

    /**
     * Get atcCode
     *
     * @return string
     */
    public function getAtcCode()
    {
        return $this->atcCode;
    }

    /**
     * Set atcDescription
     *
     * @param string $atcDescription
     *
     * @return Medicine
     */
    public function setAtcDescription($atcDescription)
    {
        $this->atcDescription = $atcDescription;

        return $this;
    }

    /**
     * Get atcDescription
     *
     * @return string
     */
    public function getAtcDescription()
    {
        return $this->atcDescription;
    }

    /**
     * Set routeAdministration
     *
     * @param string $routeAdministration
     *
     * @return Medicine
     */
    public function setRouteAdministration($routeAdministration)
    {
        $this->routeAdministration = $routeAdministration;

        return $this;
    }

    /**
     * Get routeAdministration
     *
     * @return string
     */
    public function getRouteAdministration()
    {
        return $this->routeAdministration;
    }

    /**
     * Set concentration
     *
     * @param string $concentration
     *
     * @return Medicine
     */
    public function setConcentration($concentration)
    {
        $this->concentration = $concentration;

        return $this;
    }

    /**
     * Get concentration
     *
     * @return string
     */
    public function getConcentration()
    {
        return $this->concentration;
    }

    /**
     * Set activePrinciple
     *
     * @param string $activePrinciple
     *
     * @return Medicine
     */
    public function setActivePrinciple($activePrinciple)
    {
        $this->activePrinciple = $activePrinciple;

        return $this;
    }

    /**
     * Get activePrinciple
     *
     * @return string
     */
    public function getActivePrinciple()
    {
        return $this->activePrinciple;
    }

    /**
     * Set unityMeasure
     *
     * @param string $unityMeasure
     *
     * @return Medicine
     */
    public function setUnityMeasure($unityMeasure)
    {
        $this->unityMeasure = $unityMeasure;

        return $this;
    }

    /**
     * Get unityMeasure
     *
     * @return string
     */
    public function getUnityMeasure()
    {
        return $this->unityMeasure;
    }

    /**
     * Set referenceUnit
     *
     * @param string $referenceUnit
     *
     * @return Medicine
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;

        return $this;
    }

    /**
     * Get referenceUnit
     *
     * @return string
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * Set pharmaceuticalForm
     *
     * @param string $pharmaceuticalForm
     *
     * @return Medicine
     */
    public function setPharmaceuticalForm($pharmaceuticalForm)
    {
        $this->pharmaceuticalForm = $pharmaceuticalForm;

        return $this;
    }

    /**
     * Get pharmaceuticalForm
     *
     * @return string
     */
    public function getPharmaceuticalForm()
    {
        return $this->pharmaceuticalForm;
    }
}
