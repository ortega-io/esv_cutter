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
 * @license 	CC BY-NC 4.0 (https://creativecommons.org/licenses/by-nc/4.0/)
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
	private $tableWidth;
	private $tableHeight;
	private $baseMarginLeft;
	private $baseMarginTop;
	private $entityScan;


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

	private $cvaMarginLeft;
	private $cvaMarginTop;
	private $cvaConceptColumnWidth;
	private $cvaNumericColumnWidth;
	private $cvaTextualColumnWidth;
	private $cvaRowHeight;
	private $cvaMaxRows;

	/* Lado (b) = Lado izquierdo de acta */

	private $cvbMarginLeft;
	private $cvbMarginTop;
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
		$this->tableWidth			= 2345;
		$this->tableHeight			= 3965;
		$this->baseMarginLeft 		= 0;
		$this->baseMarginTop 		= 0;
		$this->entityScan 			= false;


		/* Sección de entidades (partido politico) (e) */

		/* Lado (a) = Lado izquierdo de acta */

		$this->eaMarginLeft		= 0;
		$this->eaMarginTop		= 135;
		$this->eaWidth			= 1430;
		$this->eaHeight			= 65;
		
		/* Lado (b) = Lado derecho de acta  */

		$this->ebMarginLeft		= 1525;
		$this->ebMarginTop		= 135;
		$this->ebWidth			= 1430;
		$this->ebHeight			= 65;


		/* Sección de Conteo de Votos (cv) */
		
		/* Lado (a) = Lado izquierdo de acta */

		$this->cvaMarginLeft			= 75;
		$this->cvaMarginTop				= 135;
		$this->cvaConceptColumnWidth	= 645;
		$this->cvaNumericColumnWidth	= 305;
		$this->cvaTextualColumnWidth	= 490;
		$this->cvaRowHeight				= 80;
		$this->cvaMaxRows				= 26;


		/* Lado (b) = Lado izquierdo de acta */

		$this->cvbMarginLeft			= 1610;
		$this->cvbMarginTop				= 135;
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
    	
    	
    	/* Define si es necesario buscar entidades */

    	if( ($this->formato=='B') && (strpos($url, 'san_salvador')===false) )
    	{
    		echo ">> Requires entityScan\n";
    		$this->entityScan = true;
    	}

    	echo "> Procesando JRV: [{$this->jrv}] \t Tipo: [{$type}] \t Formato: [{$this->formato}]... ";

    	
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

		$this->eaMarginTop	+= $this->baseMarginTop;
		$this->eaMarginLeft	+= $this->baseMarginLeft;
		$this->ebMarginTop	+= $this->baseMarginTop;
		$this->ebMarginLeft	+= $this->baseMarginLeft;
		$this->cvaMarginLeft+= $this->baseMarginLeft;
		$this->cvaMarginTop += $this->baseMarginTop;
		$this->cvbMarginLeft+= $this->baseMarginLeft;
		$this->cvbMarginTop += $this->baseMarginTop;


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

    	$conteo 		= array();
		$edgeMapper 	= new EdgesMapper();


    	/* Crear directorios */
    	@mkdir("{$this->storePath}/entities_a/");
    	@mkdir("{$this->storePath}/entities_b/");
    	@mkdir("{$this->storePath}/count_concept_a/");
    	@mkdir("{$this->storePath}/count_numeric_a/");
    	@mkdir("{$this->storePath}/count_textual_a/");
    	@mkdir("{$this->storePath}/count_concept_b/");
    	@mkdir("{$this->storePath}/count_numeric_b/");
		@mkdir("{$this->storePath}/count_textual_b/");

    	/* Iterar todas las filas de la seccion */

    	
    	/* Lado (a) = Lado izquierdo de acta */

		$currentTopA 	= $this->cvaMarginTop;
		$conceptLeftA 	= $this->cvaMarginLeft;
		$numericLeftA 	= $this->cvaMarginLeft + $this->cvaConceptColumnWidth;
		$textualLeftA 	= $this->cvaMarginLeft + $this->cvaConceptColumnWidth + $this->cvaNumericColumnWidth;
		
    	/* Lado (b) = Lado derecho de acta */

		$currentTopB 	= $this->cvbMarginTop;
		$conceptLeftB 	= $this->cvbMarginLeft;
		$numericLeftB 	= $this->cvbMarginLeft + $this->cvbConceptColumnWidth;
		$textualLeftB 	= $this->cvbMarginLeft + $this->cvbConceptColumnWidth + $this->cvbNumericColumnWidth;

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



    		// Extrar entidades [START] ===================================== //
    		// ============================================================== //


			/* Detectar si el fragmento es una entidad */
			
			if($this->entityScan)
			{

		    	$blankSpaceA	= $edgeMapper->blankSpace($this->acta, $conceptLeftA, $currentTopA, $this->cvaConceptColumnWidth, $this->cvaRowHeight, $row);
		    	$blankSpaceB	= $edgeMapper->blankSpace($this->acta, $conceptLeftB, $currentTopB, $this->cvaConceptColumnWidth, $this->cvaRowHeight, $row);

			}
			elseif($row==1)
			{

		    	$blankSpaceA	= $edgeMapper->blankSpace($this->acta, $conceptLeftA, $currentTopA, $this->cvaConceptColumnWidth, $this->cvaRowHeight, $row);
		    	$blankSpaceB	= $edgeMapper->blankSpace($this->acta, $conceptLeftB, $currentTopB, $this->cvaConceptColumnWidth, $this->cvaRowHeight, $row);

			}


			if($blankSpaceA)
			{
				$tempEntityImgA 		= imagecreatetruecolor($this->eaWidth, $this->eaHeight);

				imagecopy($tempEntityImgA, $this->acta, 0, 0, $conceptLeftA, $currentTopA, $this->eaWidth, $this->eaHeight);	
				imagejpeg($tempEntityImgA, "{$this->storePath}/entities_a/row_{$row}.jpg");

				$blankSpaceA 			= false;
			}

			if($blankSpaceB)
			{
				$tempEntityImgB 		= imagecreatetruecolor($this->ebWidth, $this->ebHeight);

				imagecopy($tempEntityImgB, $this->acta, 0, 0, $conceptLeftB, $currentTopB, $this->ebWidth, $this->ebHeight);
				imagejpeg($tempEntityImgB, "{$this->storePath}/entities_b/row_{$row}.jpg");

				$blankSpaceB 			= false;

			}
   			

   			/* Generar las imagenes temporales de destino */

    		
    		$tempConceptImgB 		= imagecreatetruecolor($this->cvbConceptColumnWidth, $this->cvbRowHeight);


			// Extrar entidades [END] ======================================= //
    		// ============================================================== //



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

    	echo "[DONE]\n";
    	
    }


}