<?php

error_reporting(E_ALL);

/**
 * ordersystem - class.Bill.php
 *
 * $Id$
 *
 * This file is part of ordersystem.
 *
 * Automatically generated on 30.01.2012, 23:48:15 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include Order
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('class.Order.php');

/* user defined includes */
// section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000090D-includes begin
// section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000090D-includes end

/* user defined constants */
// section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000090D-constants begin
// section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000090D-constants end

/**
 * Short description of class Bill
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Bill
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute billNumber
     *
     * @access public
     * @var Integer
     */
    public $billNumber = null;

    /**
     * Short description of attribute items
     *
     * @access public
     * @var String
     */
    public $items = null;

    /**
     * Short description of attribute date
     *
     * @access public
     * @var String
     */
    public $date = null;

    /**
     * Short description of attribute id
     *
     * @access public
     * @var Integer
     */
    public $id = null;

    
	function __construct($billNumber, $items, $date, $id)
	{
		$this->billNumber = $billNumber;
		$this->items = $items;
		$this->date = $date;
		$this->id = $id;
		
		$this->createPDF();
	}
	
    public function createPDF()
    {
		/**require_once("includes/fpdf.php");
        
		$pdf = new FPDF('P', 'mm', array(80, 100));
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 12);
		$pdf->Image('images/logo.png', 5, 5);
		$pdf->Output();**/
		
    }

    /**
     * Short description of method Bill
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function Bill()
    {
        // section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000092C begin
        // section 10-0-0-11-7512b4a7:1352f822a0b:-8000:000000000000092C end
    }

} /* end of class Bill */

?>