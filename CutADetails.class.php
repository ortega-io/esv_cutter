<?php

/**
 * 
 * CutADetails
 * 
 * Extrae información de las actas de votación para formato A1-A8.
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * @license 	CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
 * 
 */

 
class CutADetails
{

	/* Información sobre el acta */

	private $urlActa;
	private $acta;
	private $jrv;
	private $formato;
	private $storePath;
	private $tableWidth;
	private $tableHeight;
	private $baseMarginLeft;
	private $baseMarginTop;


	/* Sección de Entidades (e) [Cabeceras de columnas] */
	
	private $eMarginLeft;
	private $eMarginTop;
	private $eConceptColumnWidth;
	private $eNumericColumnWidth;
	private $eTextualColumnWidth;
	private $eRowHeight;
	private $eMaxRows;


	/* Sección de Conteo de Votos (cv) */
	
	private $cvMarginLeft;
	private $cvMarginTop;
	private $cvConceptColumnWidth;
	private $cvNumericColumnWidth;
	private $cvTextualColumnWidth;
	private $cvRowHeight;
	private $cvMaxRows;


    /**
    * ----------------------------------------------------
    *   _construct()
    * ----------------------------------------------------
    * 
    * Crea una nueva instancia de la clase y inicializa valores.
    * 
    * @return  CutADetails
    */

    public function __construct()
    {	

		/* Información sobre el acta */

		$this->urlActa				= null;
		$this->acta					= null;
		$this->jrv					= null;
		$this->formato				= null;
		$this->storePath			= null;
		$this->tableWidth			= 2345;
		$this->tableHeight			= 3965;
		$this->baseMarginLeft 		= 0;
		$this->baseMarginTop 		= 0;


		/* Sección de Entidades (e) [Cabeceras de columnas] */

		$this->eMarginLeft			= 190;
		$this->eMarginTop			= 155;
		$this->eConceptColumnWidth	= 195;
		$this->eRowHeight			= 71;
		$this->eMaxColumns			= 11;


    	/* Sección de Conteo de Votos (cv) */
		
		$this->cvMarginLeft			= 195;
		$this->cvMarginTop			= 225;
		$this->cvNumericColumnWidth	= 192;
		$this->cvRowHeight			= 79;
		$this->cvMaxRows			= 40;

    }


    /**
    * ----------------------------------------------------
    *   setActa()
    * ----------------------------------------------------
    * 
    * Define la URL de la imagen del acta escaneada y la obtiene;
    * 
    * @param   string $url
    *
    * @return  void
    */

    public function setActa($url)
    {
    	
    	$this->urlActa 	= $url;
    	$this->acta 	= imagecreatefrompng($url);


        /* Detectar tipo de elección */

        if(strpos($this->urlActa, '/actas/2/')!==false)
        {
            $type   = 'parlacen';
        }
        elseif(strpos($this->urlActa, '/actas/3/')!==false)
        {
            $type   = 'diputados';
        }
        elseif(strpos($this->urlActa, '/actas/5/')!==false)
        {
            $type   = 'alcaldes';
        }


    	/* Manejar rotaciones */

		$imageWidth 	= ImageSX($this->acta);
		$imageHeight 	= ImageSY($this->acta);

		// Este formato debe ser vertical //
	   	if($imageWidth>$imageHeight) 
    	{
    		$this->acta = imagerotate($this->acta, 90, 0);
    	}


    	$imageName 		= pathinfo($url, PATHINFO_FILENAME);
    	$imageNameData 	= explode('_', $imageName);

    	$this->jrv 		= $imageNameData[0].'_'.$imageNameData[1];
    	$this->formato	= $imageNameData[2];
    	
		echo "> Procesando JRV: [{$this->jrv}] \t Tipo: [{$type}] \t Formato: [{$this->formato}]\n";


    	/* Crea los folderes correspondientes */
    	
    	$this->storePath = "temp/{$this->jrv}/{$type}/{$this->formato}";

    	@mkdir("temp/{$this->jrv}/");
    	@mkdir("temp/{$this->jrv}/{$type}");
    	@mkdir("temp/{$this->jrv}/{$type}/{$this->formato}");

    	
    	/* Detectar margenes y aplicar correciones */

    	$edgeMapper 	= new EdgesMapper();
    	$edges 			= $edgeMapper->getEdges($this->acta);

    	if(isset($edges['horizontalTop']))
    	{
    		$this->baseMarginTop = $edges['horizontalTop'];
    	}
    	elseif(isset($edges['horizontalBottom']))
    	{
    		
    		$estimatedTop 			= ($edges['horizontalBottom']-$this->tableHeight);
			$this->baseMarginTop 	= ($estimatedTop>0) ? $estimatedTop : 0;
    	}

    	if(isset($edges['verticalLeft']))
    	{
    		$this->baseMarginLeft = $edges['verticalLeft'];
    	}
    	elseif(isset($edges['verticalRight']))
    	{
    		$estimatedLeft 			= ($edges['verticalRight']-$this->tableWidth);
    		$this->baseMarginLeft 	= ($estimatedLeft>0) ? $estimatedLeft : 0;
    	}

    	/* Upadting */

		$this->eMarginLeft	+= $this->baseMarginLeft;
		$this->eMarginTop	+= $this->baseMarginTop;
		$this->cvMarginLeft	+= $this->baseMarginLeft;
		$this->cvMarginTop	+= $this->baseMarginTop;

    }


    /**
    * ----------------------------------------------------
    *   getConteo()
    * ----------------------------------------------------
    * 
    * Devuelve un array con el conteo de votos para cada concepto 
    * concepto = partido politico, total
    * 
    * @return  Array conteo
    */

    public function getConteo()
    {	

    	$conteo = array();


    	/* Crear directorios */

    	@mkdir("{$this->storePath}/count_concept/");
    	@mkdir("{$this->storePath}/count_numeric/");


    	/* Iterar fila de conceptos */

		$currentTop 	= $this->eMarginTop;
		$conceptLeft 	= $this->eMarginLeft;
		$currentLeft	= $conceptLeft;
		$discounted 	= false;

    	for($column=1; $column<=$this->eMaxColumns; $column++)
    	{
   
   			/* Generar las imagenes temporales de destino */

    		$tempConceptImg 		= imagecreatetruecolor($this->eConceptColumnWidth, $this->eRowHeight);


    		/* Extraer el fragmento correspondiente a cada campo */

			imagecopy($tempConceptImg, $this->acta, 0, 0, $currentLeft, $currentTop, $this->eConceptColumnWidth, $this->eRowHeight);


			/* Almacenar fragmento */

			imagejpeg($tempConceptImg, "{$this->storePath}/count_concept/column_{$column}.jpg");


			/* NOTA: Por alguna razón las columnas no tienen el mismo ancho..
			 * asi que se necesitó hacer los siguientes ajustez  
			 */

			if($column==4)
			{
				//$currentLeft += 5;
			}
			elseif($column==8)
			{
				//$currentLeft += 6;
			}

			/* Avanzar a la siguiente columna */
			$currentLeft += $this->eConceptColumnWidth;

    	}



    	/* Iterar todas la tabla de la seccion */

		$currentTop 	= $this->cvMarginTop;
		$numericLeft 	= $this->cvMarginLeft;
		$currentLeft	= $numericLeft;
		$discounted 	= false;

    	for($column=1; $column<=$this->eMaxColumns; $column++)
    	{

			$currentTop 	= $this->cvMarginTop;

	    	for($row=1; $row<=$this->cvMaxRows; $row++)
	    	{
	   
	   			/* Generar las imagenes temporales de destino */

	    		$tempNumericImg 		= imagecreatetruecolor($this->cvNumericColumnWidth, $this->cvRowHeight);


	    		/* Extraer el fragmento correspondiente a cada campo */

				imagecopy($tempNumericImg, $this->acta, 0, 0, $currentLeft, $currentTop, $this->cvNumericColumnWidth, $this->cvRowHeight);


				/* Almacenar fragmento */

				imagejpeg($tempNumericImg, "{$this->storePath}/count_numeric/cell_{$row}x{$column}.jpg");


				/* NOTA: Por alguna razón las filas no tienen el mismo alto...
				 * asi que se necesitó hacer los siguientes ajustez  
				 */


				
				if($row==4)
				{
					$currentTop 		-= 10;
				}
				elseif( ($row>5) && (($row%5)==0) )
				{
					$currentTop 		-= 5;
				}
				


				/* Avanzar a la siguiente fila */
				$currentTop += $this->cvRowHeight;

	    	}


			/* NOTA: Por alguna razón las columnas no tienen el mismo ancho..
			 * asi que se necesitó hacer los siguientes ajustez  
			 */

			if($column==4)
			{
				//$currentLeft += 12;
			}
			elseif($column==8)
			{
				//$currentLeft += 6;
			}

			/* Avanzar a la siguiente columna */
			$currentLeft += $this->eConceptColumnWidth;

	    }

    }

}