<?php

/**
 * Класс вершины
 * Class Node
 */
Class Node{

    /**
     * @var array $instances Количество инстансов вершин
     */
    public static $instances;

    /**
     * @var int номер вершины
     */
    private $number;

    /**
     * @var array массив рёбер, с которыми соеденина данная вершина
     */
    private $edges = [];

    public function __construct(int $number)
    {
        $this->number = $number;
        $number[] = $this;

        self::$instances[$this->getNumber()] = $this;
    }

    public function getNumber(){
        return $this->number;
    }

    /**
     * Соединение ноды с ребром
     * @param Edge $edge
     */
    public function attachEdge(Edge $edge){
        $this->edges[$edge->getDestinationNode($this->getNumber())->getNumber()] = $edge;
    }

    /**
     * Вернет ноду по номеру
     * @param $number
     * @return Node|null
     */
    public static function getNodeByNumber($number){
        return self::$instances[$number];
    }

    /**
     * Получить вес до соседней ноды
     * @param $neighborNumber
     * @return int|INF
     */
    public function getNeighborsWeight($neighborNumber)
    {
        /**
         * @var $edge Edge
         */
        foreach($this->edges as $edge){
            $nodeB = $edge->getDestinationNode($this->getNumber());
            if($nodeB instanceof Node && $nodeB->getNumber() === $neighborNumber){
                return $edge->getWeight();
            }
        }

        return INF;
    }

    /**
     * Вернет все номера нод, кроме той, чей номер передан
     * @param int $nodeNumber номер ноды
     */
    public static function getOtherNodesNumbers($nodeNumber)
    {
        $instances = self::$instances;

        unset($instances[$nodeNumber]);

        return array_keys($instances);
    }

}

/**
 * Класс ребра
 * Class Edge
 */
Class Edge{

    /**
     * @var array Количество инстансов рёбер
     */
    private static $instances;

    /**
     * @var int вес ребра
     */
    private $weight;

    /**
     * @var array массив двух вершин, которые соединяет ребро
     */
    private $nodes = [];

    public function __construct(Node $nodeA,Node $nodeB,int $weight)
    {
        $this->nodes = [$nodeA->getNumber() => $nodeA,$nodeB->getNumber() => $nodeB];

        $nodeA->attachEdge($this);
        $nodeB->attachEdge($this);

        $this->weight = $weight;

        self::$instances[] = $this;
    }

    /**
     * Возвращает вершину с которой связывается нода
     * @param int $startNodeNumber номер старотовой вершины
     */
    public function getDestinationNode(int $startNodeNumber)
    {
        foreach($this->nodes as $nodeNumber => $node){
            if($nodeNumber != $startNodeNumber){
                return $node;
            }
        }
    }

    /**
     * Вес ребра
     * @return int
     */
    public function getWeight(){
        return $this->weight;
    }

}


/*
 * Класс реализует алгоритм Дейкстры для заданного графа
 */
class Dijkstra
{
    /**
     * Dijkstra constructor.
     * Инициализируем все вершины и ребра
     * @param array $graph
     */
    public function __construct(array $graph)
    {
        foreach($graph as $nodeANumber => $edges)
        {
            foreach($edges as $nodeBNumber => $weight)
            {
                $nodeA = Node::getNodeByNumber($nodeANumber) ?? new Node($nodeANumber);
                $nodeB = Node::getNodeByNumber($nodeBNumber) ?? new Node($nodeBNumber);

                $edge = new Edge($nodeA, $nodeB, $weight);
            }
        }

    }


    /**
     * Реализация алгоритма Дейкстры
     * Возвращает матрицу со всеми путями для заданной вершины
     * @param int $startNodeNumber стартовая вершина
     */
    public function getRoutes(int $startNodeNumber = 1)
    {
        $startNode = Node::getNodeByNumber($startNodeNumber);
        $otherNodes = Node::getOtherNodesNumbers($startNodeNumber);

        # массив текущей итераций
        $current = [
            's' => [$startNodeNumber], # массив посященных нод
            'D' => [], # массив весов нод до посящения
        ];

        foreach($otherNodes as $nodeNumber)
        {
            /**
             * @var $node Node
             */
            $node = Node::getNodeByNumber($nodeNumber);
            $current['D'][$node->getNumber()] = $startNode->getNeighborsWeight($nodeNumber);
        }


        $result['routes'] = array_fill_keys($otherNodes, null);

        while(in_array(null, $result['routes']))
        {
            # массив предыдущей итерации
            $previous = $current;
            $current = [];

            # минимальный вес из предыдущей итерации
            $w = min($previous['D']);

            # выбираем следующую ноду в список посященных, вес которой равен текущему W
            foreach($previous['D'] as $nodeNumber => $nodeWeight){
                if($w == $nodeWeight){
                    $currentNodeNumber = $nodeNumber;
                    $current['s'] = array_merge($previous['s'], [$currentNodeNumber]);
                    $result['routes'][$currentNodeNumber] = $nodeWeight;
                    break;
                }
            }

            # получим номера всех непосященных нод
            $nextNodeNumbers = array_diff(array_keys(Node::$instances), $current['s']);

            # обновляем массив непосященных нод для текущей итерации и просчитываем новый массив D
            foreach($nextNodeNumbers as $nextNodeNumber){

                $nextNode = Node::getNodeByNumber($nextNodeNumber);
                $newVal = min(
                    Node::getNodeByNumber($currentNodeNumber)->getNeighborsWeight($nextNodeNumber) + $w,
                    $previous['D'][$nextNodeNumber]
                );

                $current['D'][$nextNode->getNumber()] = $newVal;
            }
        }

        $result['startNode'] = $startNodeNumber;
        ksort($result['routes']);

        return $result;
    }
}

$graph = [
   1 => [
       2 => 7,
       3 => 9,
       6 => 14,
   ],
    2 => [
        1 => 7,
        3 => 10,
        4 => 15
    ],
    3 => [
        1 => 9,
        2 => 10,
        4 => 11,
        6 => 2
    ],
    4 => [
        2 => 15,
        3 => 11,
        5 => 6
    ],
    5 => [
        4 => 6,
        6 => 9
    ],
    6 => [
        1 => 14,
        3 => 2,
        5 => 9
    ]
];

$dijkstra = new Dijkstra($graph);
$result = $dijkstra->getRoutes(1);

echo "Для стартовой вершины ".$result['startNode']."</br>";
foreach($result['routes'] as $nodeNumber => $minRoute){
    echo "До вершины ".$nodeNumber. " кратчайший путь составляет ".$minRoute."</br>";
}
