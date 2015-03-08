<?php

/**
 * 
 * CutExtra
 * 
 * Extrae información de las actas de votación para formato B en adelante.
 *
 * @package     EleccionesSV
 * @subpackage  Cutter
 * @author      Otoniel Ortega <ortega_x2@hotmail.com>
 * @copyright   2015 Otoniel Ortega (c)
 * @version     1.0
 * 
 */

 
class CutExtra
{

	/* URL de la imagen del acta escaneada */

	private $urlActa;
	private $acta;
	private $jrv;
	private $formato;
	private $storePath;


	/* Sección de entidades (partido politico) (e)  */

	/* Lado (a) = Lado izquierdo de acta */

	private $eaMarginLeft;
	private $eaMarginTop;
	private $eaWidth;
	private $eaHeight;
	
	/* Lado (b) = Lado derecho de acta  */

	private $ebMarginLeft;
	private $ebMarginTop;
	private $ebWidth;
	private $ebHeight;


	/* Sección de Conteo de Votos (cv) ====================================== */
	
	/* Lado (a) = Lado izquierdo de acta */

	private $cvaLeftMargin;
	private $cvaTopMargin;
	private $cvaConceptColumnWidth;
	private $cvaNumericColumnWidth;
	private $cvaTextualColumnWidth;
	private $cvaRowHeight;
	private $cvaMaxRows;

	/* Lado (b) = Lado izquierdo de acta */

	private $cvbLeftMargin;
	private $cvbTopMargin;
	private $cvbConceptColumnWidth;
	private $cvbNumericColumnWidth;
	private $cvbTextualColumnWidth;
	private $cvbRowHeight;
	private $cvbMaxRows;




    /**
    * ----------------------------------------------------
    *   _construct()
    * ----------------------------------------------------
    * 
    * Crea una nueva instancia de la clase y inicializa valores.
    * 
    * @return  CutExtra
    */

    public function __construct()
    {	

		/* URL de la imagen del acta escaneada y paths */

		$this->urlActa				= null;
		$this->acta					= null;
		$this->jrv					= null;
		$this->formato				= null;
		$this->storePath			= null;


		/* Sección de entidades (partido politico) (e) */

		/* Lado (a) = Lado izquierdo de acta */

		$this->eaMarginLeft		= 120;
		$this->eaMarginTop		= 325;
		$this->eaWidth			= 1515;
		$this->eaHeight			= 65;
		
		/* Lado (b) = Lado derecho de acta  */

		$this->ebMarginLeft		= 1645;
		$this->ebMarginTop		= 325;
		$this->ebWidth			= 1505;
		$this->ebHeight			= 65;


		/* Sección de Conteo de Votos (cv) */
		
		/* Lado (a) = Lado izquierdo de acta */

		$this->cvaLeftMargin			= 200;
		$this->cvaTopMargin				= 405;
		$this->cvaConceptColumnWidth	= 645;
		$this->cvaNumericColumnWidth	= 305;
		$this->cvaTextualColumnWidth	= 490;
		$this->cvaRowHeight				= 80;
		$this->cvaMaxRows				= 26;

		/* Lado (b) = Lado izquierdo de acta */

		$this->cvbLeftMargin			= 1735;
		$this->cvbTopMargin				= 405;
		$this->cvbConceptColumnWidth	= 640;
		$this->cvbNumericColumnWidth	= 300;
		$this->cvbTextualColumnWidth	= 480;
		$this->cvbRowHeight				= 80;
		$this->cvbMaxRows				= 26;

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

		echo "EXTRA: $this->storePath \n";

    	@mkdir("temp/{$this->jrv}/");
    	@mkdir("temp/{$this->jrv}/{$this->formato}");


    }


    /**
    * ----------------------------------------------------
    *   getConteo()
    * ----------------------------------------------------
    * 
    * Devuelve un array con el conteo de votos para cada concepto 
    * concepto = candidato
    * 
    * @return  Array conteo
    */

    public function getConteo()
    {	

    	$conteo = array();

    	/* Crear directorios */

    	@mkdir("{$this->storePath}/count_concept_a/");
    	@mkdir("{$this->storePath}/count_numeric_a/");
    	@mkdir("{$this->storePath}/count_textual_a/");
    	@mkdir("{$this->storePath}/count_concept_b/");
    	@mkdir("{$this->storePath}/count_numeric_b/");
		@mkdir("{$this->storePath}/count_textual_b/");

    	/* Iterar todas las filas de la seccion */

    	
    	/* Lado (a) = Lado izquierdo de acta */

		$currentTopA 	= $this->cvaTopMargin;
		$conceptLeftA 	= $this->cvaLeftMargin;
		$numericLeftA 	= $this->cvaLeftMargin + $this->cvaConceptColumnWidth;
		$textualLeftA 	= $this->cvaLeftMargin + $this->cvaConceptColumnWidth + $this->cvaNumericColumnWidth;
		
    	/* Lado (b) = Lado derecho de acta */

		$currentTopB 	= $this->cvbTopMargin;
		$conceptLeftB 	= $this->cvbLeftMargin;
		$numericLeftB 	= $this->cvbLeftMargin + $this->cvbConceptColumnWidth;
		$textualLeftB 	= $this->cvbLeftMargin + $this->cvbConceptColumnWidth + $this->cvbNumericColumnWidth;

		$discounted 	= false;


    	for($row=1; $row<=$this->cvaMaxRows; $row++)
    	{
   
   			/* Generar las imagenes temporales de destino */

    		$tempConceptImgA 		= imagecreatetruecolor($this->cvaConceptColumnWidth, $this->cvaRowHeight);
    		$tempNumericImgA 		= imagecreatetruecolor($this->cvaNumericColumnWidth, $this->cvaRowHeight);
    		$tempTextualImgA 		= imagecreatetruecolor($this->cvaTextualColumnWidth, $this->cvaRowHeight);

    		$tempConceptImgB 		= imagecreatetruecolor($this->cvbConceptColumnWidth, $this->cvbRowHeight);
    		$tempNumericImgB 		= imagecreatetruecolor($this->cvbNumericColumnWidth, $this->cvbRowHeight);
    		$tempTextualImgB 		= imagecreatetruecolor($this->cvbTextualColumnWidth, $this->cvbRowHeight);


    		/* Extraer el fragmento correspondiente a cada campo */

			imagecopy($tempConceptImgA, $this->acta, 0, 0, $conceptLeftA, $currentTopA, $this->cvaConceptColumnWidth, $this->cvaRowHeight);
			imagecopy($tempNumericImgA, $this->acta, 0, 0, $numericLeftA, $currentTopA, $this->cvaNumericColumnWidth, $this->cvaRowHeight);
			imagecopy($tempTextualImgA, $this->acta, 0, 0, $textualLeftA, $currentTopA, $this->cvaTextualColumnWidth, $this->cvaRowHeight);

			imagecopy($tempConceptImgB, $this->acta, 0, 0, $conceptLeftB, $currentTopB, $this->cvbConceptColumnWidth, $this->cvbRowHeight);
			imagecopy($tempNumericImgB, $this->acta, 0, 0, $numericLeftB, $currentTopB, $this->cvbNumericColumnWidth, $this->cvbRowHeight);
			imagecopy($tempTextualImgB, $this->acta, 0, 0, $textualLeftB, $currentTopB, $this->cvbTextualColumnWidth, $this->cvbRowHeight);


			/* Almacenar fragmento */

			imagejpeg($tempConceptImgA, "{$this->storePath}/count_concept_a/row_{$row}.jpg");
			imagejpeg($tempNumericImgA, "{$this->storePath}/count_numeric_a/row_{$row}.jpg");
			imagejpeg($tempTextualImgA, "{$this->storePath}/count_textual_a/row_{$row}.jpg");

			imagejpeg($tempConceptImgB, "{$this->storePath}/count_concept_b/row_{$row}.jpg");
			imagejpeg($tempNumericImgB, "{$this->storePath}/count_numeric_b/row_{$row}.jpg");
			imagejpeg($tempTextualImgB, "{$this->storePath}/count_textual_b/row_{$row}.jpg");



			/* NOTA: Por alguna razón las filas no tienen el mismo alto...
			 * asi que se necesitó hacer los siguientes ajustez  
			*/

			if( ($row%4)==0 )
			{
				$currentTopA 		-= 12;
				$currentTopB 		-= 12;
			}

			if($row==10)
			{
				$currentTopA 		-= 5;
				$currentTopB 		-= 5;
			}
			elseif( ($row==11) || ($row==13) || ($row==14) || ($row==19) )
			{
				$currentTopA 		-= 10;
				$currentTopB 		-= 10;
			}


			/* Avanzar a la siguiente fila */
			$currentTopA += $this->cvaRowHeight;
			$currentTopB += $this->cvbRowHeight;

    	}

    }


}