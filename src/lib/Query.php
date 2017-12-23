<?php
/**
 * Query
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * Query
 *
 * @package SEOSmuggler
 */
class Query
{
    /** @var [\SEOSmuggler\Query] $queries Query composite */
    private $queries = [];
    
    /** @var array $conditions Conditions */
    private $conditions = [];
    
    /** @var string $logical Logical operator */
    private $logical;
    
    /**
     * Query constructor
     * 
     * @param string $logical Logical operator
     */
    public function __construct(string $logical = '&&')
    {
        $this->logical = $logical;
    }
    
    /**
     * Create query from string
     * 
     * @todo Implement this method
     * @param string $query Query string
     * @return \SEOSmuggler\Query
     */
    public static function fromString(string $query)
    {
        
    }
    
    /**
     * Add a condition
     * 
     * @param string $attribute Condition attribute.
     * @param string $operator Condition operator "!", "!=", "=", "<", "<=", ">", ">="
     * @param mixed $value Condition value
     * @param string $logical Logical operator && or ||
     */
    public function addCondition(string $attribute, string $operator = null, $value = null, string $logical = '&&')
    {
       array_push($this->conditions, ['attribute' => $attribute, 'operator' => $operator, 'value' => $value, 'logical' => $logical]);
    }
    
    /**
     * Add query as a composite
     * 
     * @param \SEOSmuggler\Query $query Query object
     */
    public function addQuery(Query $query)
    {
        array_push($this->queries, $query);
    }
    
    /**
     * Execute the query
     * 
     * @param array $datas Source datas
     * @return array Datas matching the query
     */
    public function doQuery(array $datas)
    {
        $index = 0;
        $validated = [];
        $validItems = [];
        $countConditions = count($this->conditions);
        foreach ($this->conditions as $condition) {
            // La condition précédente a retourné un résultat vide et l'opérateur logique est &&
            if ($index > 0 && $condition['logical'] === '&&' && count($validated[$index-1]) === 0) {
                return array();
            }
            // Résultat de la condition
            $conditionResult = $this->testCondition($condition, $datas);
            // SI la condition actuel échoue et que l'opérateur logique est &&
            if ($index > 0 && $condition['logical'] === '&&' && count($conditionResult) === 0) {
                return array();
            // La condition actuelle et la précédente on renvoyé des résultats on va merger les deux tableaux
            } elseif ($index > 0 && $condition['logical'] === '&&') {
                $validItems = array_intersect($conditionResult, $validated[$index-1]);
            // On Fusionnes les résultats de l'opératuer logique ||
            } elseif ($index > 0 && $condition['logical'] === '||') {
                $validItems = array_unique(array_merge($conditionResult, $validated[$index-1]));
            } elseif ($countConditions === 1) {
                $validItems = $conditionResult;
            }
            array_push($validated, $conditionResult);
            $index++;
        }
        $return = array();
        foreach ($validItems as $url) {
            $return[$url] = $datas[$url];
        }
        
        if ($countConditions === 0) {
            $return = $datas;
        }
        // Query composite
        foreach ($this->queries as $query) {
            
            if ($query->logical == '&&' && count($return) === 0 ) {
                return array();
            }
            $queryDatas = $query->doQuery($datas);
            if ($query->logical == '&&' && count($queryDatas) === 0 ) {
                return array();
            } elseif ($query->logical === '&&') {
                $return = array_intersect($queryDatas, $return);
            } elseif ($condition['logical'] === '||') {
                $return = array_unique(array_merge($queryDatas, $return));
            }            
        }
        return $return;        
    }
    
    /**
     * Test a condition
     * 
     * @param array $condition Condition settings array
     * @param array $datas Source datas
     * @return array 
     */
    private function testCondition(array $condition, array $datas)
    {
        $validated = [];
        foreach($datas as $url => $item){
            $value = $item->get($condition['attribute']);
            $valid = false;
            switch ($condition['operator']) {
                case null:
                    if($value !== null){
                        $valid = true;
                    }
                    break;
                case '!':
                    if($value === null){
                        $valid = true;
                    }
                    break;
                case '!=':
                    if($value != $condition['value']){
                        $valid = true;
                    }
                    break;
                case '=':
                    if($value == $condition['value']){
                        $valid = true;
                    }
                    break;
                case '<':
                    if($value < $condition['value']){
                        $valid = true;
                    }
                    break;
                case '<=':
                    if($value <= $condition['value']){
                        $valid = true;
                    }
                    break;
                case '>':
                    if($value > $condition['value']){
                        $valid = true;
                    }
                    break;
                case '>=':
                    if($value >= $condition['value']){
                        $valid = true;
                    }
                    break;
            }                
            if($valid === true){
                array_push($validated, $url);
            }            
        }
        return $validated;
    }
}