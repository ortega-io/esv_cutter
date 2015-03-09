<?php

/**
 * 
 * EdgesMapper
 * 
 * Detecta los bordes de la tabla en un acta.
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * @license 	CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
 * 
 */

 
class EdgesMapper
{

	/* Acta escaneada */

	private $acta;


	/* Limitar escaneo a un determinado porcentaje */

	private $verticalLimit;
	private $horizontalLimit;


    /**
    * ----------------------------------------------------
    *   _construct()
    * ----------------------------------------------------
    * 
    * Crea una nueva instancia de la clase y inicializa valores.
    * 
    */

    public function __construct()
    {	
		
		/* Acta escaneada */

		$this->acta					= null;

		
		/* Limitar escaneo a un determinado porcentaje */

		$this->verticalLimit	= 0.20;
		$this->horizontalLimit	= 0.20;

    }



    /**
    * ----------------------------------------------------
    *   getEdges($acta)
    * ----------------------------------------------------
    * 
    * Devuelve las coordenadas de las lineas de borde de la tabla.
    * 
    * @param IMAGE_RESOURCE $acta
    * 
    * return Array edges
    */

    public function getEdges($acta)
    {	

    	$this->acta = $acta;

		$imageWidth 	= ImageSX($this->acta);
		$imageHeight 	= ImageSY($this->acta);

		if( ($imageWidth==false) || ($imageHeight==false) )
		{
			return false;
		}

		$scanZonesVertical 		= array
		(

			'verticalLeft' 		=> array( 0, ceil($imageWidth*$this->horizontalLimit), 0, ceil($imageHeight/2) ),
			'verticalRight' 	=> array( ($imageWidth - ceil($imageWidth*$this->horizontalLimit)), ($imageWidth-1), 0, ceil($imageHeight/2) )
		);

		$scanZonesHorizontal	= array
		(
			'horizontalTop' 	=> array( 0, ceil($imageWidth/2), 0, ceil($imageHeight*$this->verticalLimit) ),
			'horizontalBottom' 	=> array( ceil($imageWidth/2), ($imageWidth-1), ($imageHeight-ceil($imageHeight*$this->verticalLimit)), ($imageHeight-1) )
		);

		$borders = array();


		foreach ($scanZonesHorizontal as $zone => $coordinates)
		{

			for($y = $coordinates[2]; $y<=$coordinates[3]; $y++)
			{

				$activePixels 	= array();

				for($x = $coordinates[0]; $x<= $coordinates[1]; $x++)
				{

					$rgb 	= imagecolorat($acta, $x, $y);
					
					$r 		= ($rgb >> 16) & 0xFF;
					$g 		= ($rgb >> 8) & 0xFF;
					$b 		= $rgb & 0xFF;

					if(($r+$g+$b)==0)
					{
						$activePixels[]	= 1;
					}
					else
					{
						$activePixels[]	= 0;
					}
					
				}

				/* Check if we found a line */

				$totalBlack 	= array_sum($activePixels);
				$totalCells 	= count($activePixels);

				if( array_sum($activePixels) >= ceil(count($activePixels)*0.9) )
				{
					$borders[$zone] = $y;
					break;
				}

			}

		}
	
		// print_r($scanZonesVertical);

		foreach ($scanZonesVertical as $zone => $coordinates)
		{
			
			for($x = $coordinates[0]; $x<= $coordinates[1]; $x++)
			{

				$activePixels 	= array();

				for($y = $coordinates[2]; $y<=$coordinates[3]; $y++)
				{
					
					$rgb 	= imagecolorat($acta, $x, $y);
					
					$r 		= ($rgb >> 16) & 0xFF;
					$g 		= ($rgb >> 8) & 0xFF;
					$b 		= $rgb & 0xFF;

					if(($r+$g+$b)==0)
					{
						$activePixels[]	= 1;
					}
					else
					{
						$activePixels[]	= 0;
					}

				}

				/* Check if we found a line */

				$totalBlack 	= array_sum($activePixels);
				$totalCells 	= count($activePixels);

				if( array_sum($activePixels) >= ceil(count($activePixels)*0.8) )
				{
					$borders[$zone] = $x;
					break;
				}

			}
		}

		return $borders;
    }



}


?>