<?php
namespace VisoftMailerModule\Controller\Plugin;

class DetectCsvFileDelimiter extends \Zend\Mvc\Controller\Plugin\AbstractPlugin
{
	public function __invoke($csvFilePath, $checkLines = 2) 
	{
		$file = new \SplFileObject($csvFilePath);
        $delimiters = [
        	',', 
        	'\t', 
        	';', 
        	'|', 
        	':'
        ];
        $results = array();
        $i = 0;
         while($file->valid() && $i <= $checkLines){
            $line = $file->fgets();
            foreach ($delimiters as $delimiter){
                $regExp = '/['.$delimiter.']/';
                $fields = preg_split($regExp, $line);
                if(count($fields) > 1){
                    if(!empty($results[$delimiter])){
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }   
                }
            }
           $i++;
        }
        $results = array_keys($results, max($results));
        return $results[0];
	}
}

