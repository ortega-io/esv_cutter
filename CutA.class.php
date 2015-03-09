<?php

/**
 * 
 * CutA
 * 
 * Extrae información de las actas de votación formato A.
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * @license     CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
 * 
 */

 
class CutA
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


	/* Sección de Conteo de Votos (cv) */
	
	private $cvMarginLeft;
	private $cvMarginTop;
	private $cvConceptColumnWidth;
	private $cvNumericColumnWidth;
	private $cvTextualColumnWidth;
	private $cvRowHeight;
	private $cvMaxRows;


	/* Sección de Totales (st) */

	private $stMarginLeft;
	private $stMarginTop;
	private $stConceptColumnWidth;
	private $stNumericColumnWidth;
	private $stTextualColumnWidth;
	private $stRowHeight;
	private $stMaxRows;



    /**
    * ----------------------------------------------------
    *   _construct()
    * ----------------------------------------------------
    * 
    * Crea una nueva instancia de la clase y inicializa valores.
    * 
    * @return  CutA
    */

    public function __construct()
    {	
		
		/* Información sobre el acta */

		$this->urlActa				= null;
		$this->acta					= null;
		$this->jrv					= null;
		$this->formato				= null;
		$this->storePath			= null;
		$this->tableWidth			= 3960;
		$this->tableHeight			= 2185;
		$this->baseMarginLeft 		= 0;
		$this->baseMarginTop 		= 0;

    	
    	/* Sección de Conteo de Votos (cv) */
		
		$this->cvMarginLeft			= 220;
		$this->cvMarginTop			= 125;
		$this->cvConceptColumnWidth	= 279;
		$this->cvNumericColumnWidth	= 285;
		$this->cvTextualColumnWidth	= 475;
		$this->cvRowHeight			= 85;
		$this->cvLineHeight			= 10;
		$this->cvMaxRows			= 24;
	

		/* Sección de Totales (st) */

		$this->stMarginLeft			= 0;
		$this->stMarginTop			= 1945;
		$this->stConceptColumnWidth	= 490;
		$this->stNumericColumnWidth	= 295;
		$this->stTextualColumnWidth	= 475;
		$this->stRowHeight			= 75;
		$this->stMaxRows			= 3;

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

		// Este formato debe ser horizontal //
	   	if($imageHeight>$imageWidth) 
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

		$this->cvMarginLeft	+= $this->baseMarginLeft;
		$this->cvMarginTop	+= $this->baseMarginTop;
		$this->stMarginLeft	+= $this->baseMarginLeft;
		$this->stMarginTop	+= $this->baseMarginTop;

    }


    /**
    * ----------------------------------------------------
    *   getConteo()
    * ----------------------------------------------------
    * 
    * Devuelve un array con el conteo de votos para cada concepto 
    * conteo = sobrantes, impugnados, validos, etc... 
    * 
    * @return  Array conteo
    */

    public function getConteo()
    {	

    	$conteo = array();

    	
    	/* Crear directorios */

    	@mkdir("{$this->storePath}/count_concept/");
    	@mkdir("{$this->storePath}/count_numeric/");
    	@mkdir("{$this->storePath}/count_textual/");


    	/* Iterar todas las filas de la seccion */

		$currentTop 	= $this->cvMarginTop;
		$conceptLeft 	= $this->cvMarginLeft;
		$numericLeft 	= $this->cvMarginLeft + $this->cvConceptColumnWidth;
		$textualLeft 	= $this->cvMarginLeft + $this->cvConceptColumnWidth + $this->cvNumericColumnWidth;
		$discounted 	= false;

    	for($row=1; $row<=$this->cvMaxRows; $row++)
    	{
   
   			/* Generar las imagenes temporales de destino */

    		$tempConceptImg 		= imagecreatetruecolor($this->cvConceptColumnWidth, $this->cvRowHeight);
    		$tempNumericImg 		= imagecreatetruecolor($this->cvNumericColumnWidth, $this->cvRowHeight);
    		$tempTextualImg 		= imagecreatetruecolor($this->cvTextualColumnWidth, $this->cvRowHeight);

    		/* Extraer el fragmento correspondiente a cada campo */

			imagecopy($tempConceptImg, $this->acta, 0, 0, $conceptLeft, $currentTop, $this->cvConceptColumnWidth, $this->cvRowHeight);
			imagecopy($tempNumericImg, $this->acta, 0, 0, $numericLeft, $currentTop, $this->cvNumericColumnWidth, $this->cvRowHeight);
			imagecopy($tempTextualImg, $this->acta, 0, 0, $textualLeft, $currentTop, $this->cvTextualColumnWidth, $this->cvRowHeight);

			/* Almacenar fragmento */

			imagejpeg($tempConceptImg, "{$this->storePath}/count_concept/row_{$row}.jpg");
			imagejpeg($tempNumericImg, "{$this->storePath}/count_numeric/row_{$row}.jpg");
			imagejpeg($tempTextualImg, "{$this->storePath}/count_textual/row_{$row}.jpg");


			/* NOTA: Por alguna razón las filas no tienen el mismo alto...
			 * asi que se necesitó hacer los siguientes ajustez  
			 */

			if( ($row>1) && (!$discounted) )
			{
				$this->cvRowHeight 	= $this->cvRowHeight-5;
				$discounted 		= true;
			}
			elseif($row==8)
			{
				$currentTop 		-= 12;
				$this->cvRowHeight 	 = $this->cvRowHeight-5;
			}
			elseif( ($row==12) || ($row==17) )
			{
				$currentTop -= 10;
			}

			/* Avanzar a la siguiente fila */
			$currentTop += $this->cvRowHeight;

    	}

    }



    /**
    * ----------------------------------------------------
    *   getTotales()
    * ----------------------------------------------------
    * 
    * Devuelve un array con los totales  
    * (sumatoria de papeletas)
    * 
    * @return  Array totales
    */

    public function getTotales()
    {	

    	$totales = array();


    	/* Crear directorios */

    	@mkdir("{$this->storePath}/totales_concept/");
    	@mkdir("{$this->storePath}/totales_numeric/");
    	@mkdir("{$this->storePath}/totales_textual/");


    	/* Iterar todas las filas de la seccion */

		$currentTop 	= $this->stMarginTop;
		$conceptLeft 	= $this->stMarginLeft;
		$numericLeft 	= $this->stMarginLeft + $this->stConceptColumnWidth;
		$textualLeft 	= $this->stMarginLeft + $this->stConceptColumnWidth + $this->stNumericColumnWidth;
		$discounted 	= false;

    	for($row=1; $row<=$this->stMaxRows; $row++)
    	{
   
   			/* Generar las imagenes temporales de destino */

    		$tempConceptImg 		= imagecreatetruecolor($this->stConceptColumnWidth, $this->stRowHeight);
    		$tempNumericImg 		= imagecreatetruecolor($this->stNumericColumnWidth, $this->stRowHeight);
    		$tempTextualImg 		= imagecreatetruecolor($this->stTextualColumnWidth, $this->stRowHeight);


    		/* Extraer el fragmento correspondiente a cada campo */

			imagecopy($tempConceptImg, $this->acta, 0, 0, $conceptLeft, $currentTop, $this->stConceptColumnWidth, $this->stRowHeight);
			imagecopy($tempNumericImg, $this->acta, 0, 0, $numericLeft, $currentTop, $this->stNumericColumnWidth, $this->stRowHeight);
			imagecopy($tempTextualImg, $this->acta, 0, 0, $textualLeft, $currentTop, $this->stTextualColumnWidth, $this->stRowHeight);


			/* Almacenar fragmento */

			imagejpeg($tempConceptImg, "{$this->storePath}/totales_concept/row_{$row}.jpg");
			imagejpeg($tempNumericImg, "{$this->storePath}/totales_numeric/row_{$row}.jpg");
			imagejpeg($tempTextualImg, "{$this->storePath}/totales_textual/row_{$row}.jpg");


			/* NOTA: Por alguna razón las filas no tienen el mismo alto...
			 * asi que se necesitó hacer los siguientes ajustez  
			 */

			if( ($row>1) && (!$discounted) )
			{
				$currentTop +=  10;
				$discounted  = true;
			}

			/* Avanzar a la siguiente fila */
			$currentTop += $this->cvRowHeight;

    	}

    }
}