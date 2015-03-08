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
 * 
 */

 
class CutADetails
{

	/* URL de la imagen del acta escaneada */

	private $urlActa;
	private $acta;
	private $jrv;
	private $formato;
	private $storePath;


	/* Sección de Entidades (e) [Cabeceras de columnas] */
	
	private $eLeftMargin;
	private $eTopMargin;
	private $eConceptColumnWidth;
	private $eNumericColumnWidth;
	private $eTextualColumnWidth;
	private $eRowHeight;
	private $eMaxRows;


	/* Sección de Conteo de Votos (cv) */
	
	private $cvLeftMargin;
	private $cvTopMargin;
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

		/* URL de la imagen del acta escaneada y paths */

		$this->urlActa				= null;
		$this->acta					= null;
		$this->jrv					= null;
		$this->formato				= null;
		$this->storePath			= null;


		/* Sección de Entidades (e) [Cabeceras de columnas] */

		$this->eLeftMargin			= 298;
		$this->eTopMargin			= 260;
		$this->eConceptColumnWidth	= 192;
		$this->eRowHeight			= 71;
		$this->eMaxColumns			= 11;


    	/* Sección de Conteo de Votos (cv) */
		
		$this->cvLeftMargin			= 298;
		$this->cvTopMargin			= 330;
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

		$currentTop 	= $this->eTopMargin;
		$conceptLeft 	= $this->eLeftMargin;
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
				$currentLeft += 12;
			}
			elseif($column==8)
			{
				$currentLeft += 6;
			}

			/* Avanzar a la siguiente columna */
			$currentLeft += $this->eConceptColumnWidth;

    	}



    	/* Iterar todas la tabla de la seccion */

		$currentTop 	= $this->cvTopMargin;
		$numericLeft 	= $this->cvLeftMargin;
		$currentLeft	= $numericLeft;
		$discounted 	= false;

    	for($column=1; $column<=$this->eMaxColumns; $column++)
    	{

			$currentTop 	= $this->cvTopMargin;

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
				$currentLeft += 12;
			}
			elseif($column==8)
			{
				$currentLeft += 6;
			}

			/* Avanzar a la siguiente columna */
			$currentLeft += $this->eConceptColumnWidth;

	    }

    }

}