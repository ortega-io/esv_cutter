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
 * 
 */

 
class CutA
{

	/* URL de la imagen del acta escaneada y paths */

	private $urlActa;
	private $acta;
	private $jrv;
	private $formato;
	private $storePath;


	/* Sección de Conteo de Votos (cv) */
	
	private $cvLeftMargin;
	private $cvTopMargin;
	private $cvConceptColumnWidth;
	private $cvNumericColumnWidth;
	private $cvTextualColumnWidth;
	private $cvRowHeight;
	private $cvMaxRows;


	/* Sección de Totales (st) */

	private $stLeftMargin;
	private $stTopMargin;
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

		/* URL de la imagen del acta escaneada y paths */

		$this->urlActa				= null;
		$this->acta					= null;
		$this->jrv					= null;
		$this->formato				= null;
		$this->storePath			= null;


    	/* Sección de Conteo de Votos (cv) */
		
		$this->cvLeftMargin			= 315;
		$this->cvTopMargin			= 310;
		$this->cvConceptColumnWidth	= 279;
		$this->cvNumericColumnWidth	= 285;
		$this->cvTextualColumnWidth	= 475;
		$this->cvRowHeight			= 85;
		$this->cvLineHeight			= 10;
		$this->cvMaxRows			= 24;
	

		/* Sección de Totales (st) */

		$this->stLeftMargin			= 95;
		$this->stTopMargin			= 2120;
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

    	$imageName 		= pathinfo($url, PATHINFO_FILENAME);
    	$imageNameData 	= explode('_', $imageName);

    	$this->jrv 		= $imageNameData[0].'_'.$imageNameData[1];
    	$this->formato	= $imageNameData[2];
    	
    	
    	/* Crea los folderes correspondientes */
    	
    	$this->storePath = "temp/{$this->jrv}/{$this->formato}";

    	@mkdir("temp/{$this->jrv}/");
    	@mkdir("temp/{$this->jrv}/{$this->formato}");


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

		$currentTop 	= $this->cvTopMargin;
		$conceptLeft 	= $this->cvLeftMargin;
		$numericLeft 	= $this->cvLeftMargin + $this->cvConceptColumnWidth;
		$textualLeft 	= $this->cvLeftMargin + $this->cvConceptColumnWidth + $this->cvNumericColumnWidth;
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

		$currentTop 	= $this->stTopMargin;
		$conceptLeft 	= $this->stLeftMargin;
		$numericLeft 	= $this->stLeftMargin + $this->stConceptColumnWidth;
		$textualLeft 	= $this->stLeftMargin + $this->stConceptColumnWidth + $this->stNumericColumnWidth;
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