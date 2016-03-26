<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 21.03.2016
 * Time: 20:25
 */

/**
 * Class Labyrinth
 */
class Labyrinth
{
    /**
     * Ошибка в случае некорректного масиива координат
     */
    const ERROR_ARRAY = 'Array error';

    /**
     * Ошибка при некорретном назначении точки старта
     */
    const ERROR_START = 'Start point error';

    /**
     * Ошибка при некорретном назначении точки финиша
     */
    const ERROR_FINISH = 'Finish point error';

    /**
     * Ошибка означает, что невозможно пройти до точки финиша
     */
    const ERROR_PATH = 'Path error';

    /**
     * Тип путь
     */
    const TYPE_PATH = 0;

    /**
     * Тип стена
     */
    const TYPE_BLOCK = 1;

    /**
     * Тип точки старта
     */
    const TYPE_START = 2;

    /**
     * Тип точки финиша
     */
    const TYPE_FINISH = 3;

    /**
     * Символ точки старта
     */
    const SYMBOL_START = 'A';

    /**
     * Символ точки финиша
     */
    const SYMBOL_FINISH = 'B';

    /**
     * Размер лабиринта по оси x
     * @var int
     */
    private $_sizeX = 10;

    /**
     * Размер лабиринта по оси Y
     * @var int
     */
    private $_sizeY = 10;

    /**
     * Массив точек лабиринта
     * Точки хранятся в виде массива:
     * array (size=5)
     *  'x' => int 0 координта x
     *  'y' => int 0 координата y
     *  'type' => int 1 тип точки
     *  'value' => null значение шага при прохождении
     *  'is_path' => null является ли точка путем
     * @var array
     */
    private $_array = array();

    /**
     * Точка старта
     * @var array
     */
    private $_startPoint = array();

    /**
     * Точка финиша
     * @var array
     */
    private $_finishPoint = array();

    /**
     * Указывает найден ли путь прохождения лабиринта
     * @var bool
     */
    private $_ready = false;

    /**
     * Labyrinth constructor.
     * @param array $array
     * @throws Exception ошибка корректности массива
     */
    public function __construct(array $array)
    {
        if (empty($array)) {
            throw new Exception($this::ERROR_ARRAY);
        }

        for ($x = 0; $x < $this->_sizeX; $x++) {
            for ($y = 0; $y < $this->_sizeY; $y++) {
                if (isset($array[$x][$y])
                    and ($array[$x][$y] === $this::TYPE_PATH or $array[$x][$y] === $this::TYPE_BLOCK)
                ) {
                    $this->_array[$x][] = array(
                        'x' => $x,
                        'y' => $y,
                        'type' => $array[$x][$y],
                        'value' => null,
                        'is_path' => null
                    );
                } else {
                    throw new Exception($this::ERROR_ARRAY);
                }
            }
        }
    }

    /**
     * Возвращает точку старта
     * @return array
     */
    public function getStartPoint()
    {
        return $this->_startPoint;
    }

    /**
     * Возвращает точку финиша
     * @return array
     */
    public function getFinishPoint()
    {
        return $this->_finishPoint;
    }

    /**
     * Устанавливаем точку старта
     * @param $x int координата x
     * @param $y int координата y
     * @throws Exception невозможно задать точку старта в указанных координатах
     */
    public function setStartPoint($x, $y)
    {
        $point = $this->getPoint($x, $y);
        $type = $this->getPointType($point);
        if (is_array($point) and $type !== $this::TYPE_BLOCK and $point !== $this->_finishPoint) {
            if (isset($this->_startPoint) and is_array($this->_startPoint)) {
                $this->setPointType($this->_startPoint, $this::TYPE_PATH);
            }
            $this->setPointType($point, $this::TYPE_START);
        } else {
            throw new Exception ($this::ERROR_START);
        }
    }

    /**
     * Устанавливаем точку финиша
     * @param $x int координата x
     * @param $y int координата y
     * @throws Exception невозможно задать точку финиша в указанных координатах
     */
    public function setFinishPoint($x, $y)
    {
        $point = $this->getPoint($x, $y);
        $type = $this->getPointType($point);
        if (is_array($point) and $type !== $this::TYPE_BLOCK and $point !== $this->_startPoint) {
            if (isset($this->_finishPoint) and is_array($this->_finishPoint)) {
                $this->setPointType($this->_finishPoint, $this::TYPE_PATH);
            }
            $this->setPointType($point, $this::TYPE_FINISH);
        } else {
            throw new Exception ($this::ERROR_FINISH);
        }
    }

    /**
     * Возвращает точку на основе координат xy
     * @param $x int координата x
     * @param $y int координата y
     * @return array
     */
    private function getPoint($x, $y)
    {
        return (isset($this->_array[$x][$y])) ? $this->_array[$x][$y] : array();
    }

    /**
     * Возвращает тип точки
     * @param array $point
     * @return mixed|null
     */
    private function getPointType(array $point)
    {
        $typesArray = array(
            $this::TYPE_PATH,
            $this::TYPE_BLOCK,
            $this::TYPE_START,
            $this::TYPE_FINISH
        );
        return (isset($point['type']) and in_array($point['type'], $typesArray)) ? $point['type'] : null;
    }

    /**
     * Задает тип для точки
     * @param array $point
     * @param $type
     */
    private function setPointType(array $point, $type)
    {
        $x = $this->getPointCoordinate($point, 'x');
        $y = $this->getPointCoordinate($point, 'y');
        if ($x !== null and $y !== null and isset($this->_array[$x][$y])) {
            $this->_array[$x][$y]['type'] = $type;
            if ($type === $this::TYPE_START) {
                $this->_startPoint = $this->_array[$x][$y];
            } elseif ($type === $this::TYPE_FINISH) {
                $this->_finishPoint = $this->_array[$x][$y];
            }
        }
    }

    /**
     * Возвращает значение шага при прохождении
     * @param array $point
     * @return mixed|null
     */
    private function getPointValue(array $point)
    {
        return isset($point['value']) ? $point['value'] : null;
    }

    /**
     * Задает значение шага при прохождении
     * @param array $point
     * @param $value
     */
    private function setPointValue(array $point, $value)
    {
        $x = $this->getPointCoordinate($point, 'x');
        $y = $this->getPointCoordinate($point, 'y');
        $type = $this->getPointType($point);
        if ($x !== null and $y !== null and isset($this->_array[$x][$y])) {
            $this->_array[$x][$y]['value'] = $value;
        }
        if ($type === $this::TYPE_START) {
            $this->_startPoint['value'] = $value;
        } elseif ($type === $this::TYPE_FINISH) {
            $this->_finishPoint['value'] = $value;
        }
    }

    /**
     * Возвращает координату x или y для точки
     * @param array $point
     * @param string $coordinate
     * @return mixed|null
     */
    private function getPointCoordinate(array $point, $coordinate = 'x')
    {
        return (isset($point[$coordinate]) and is_numeric($point[$coordinate])) ? $point[$coordinate] : null;
    }

    /**
     * Постепенно проходим от точки старта до финиша по всем возможным путям,
     * проставляем значение для каждого шага
     * @param array $points
     * @param int $value
     */
    private function setValues(array $points, $value = 0)
    {
        if (empty($points)) {
            return;
        }

        $nextPoints = array();
        foreach ($points as $point) {
            $this->setPointValue($point, $value);
            if ($this->getPointType($point) === $this::TYPE_FINISH) {
                // если дошли до финиша, то запоминаем, что лабиринт пройден и завершаем работу функции
                $this->_ready = true;
                return;
            }
            foreach ($this->getNextPoints($point) as $nextPoint) {
                if ($this->getPointValue($nextPoint) === null) {
                    $nextPoints[] = $nextPoint;
                }
            }
        }

        $this->setValues($nextPoints, $value + 1);
    }

    /**
     * Возвращает точки, на которые можно перейти с точки point
     * @param array $point
     * @return array
     */
    private function getNextPoints(array $point)
    {
        if (!$this->typeIsAllowed($this->getPointType($point))) {
            return array();
        }

        $nextPoints = array();
        $x = $this->getPointCoordinate($point, 'x');
        $y = $this->getPointCoordinate($point, 'y');
        if ($x !== null and $y !== null) {
            $nearbyPoints = array(
                $this->getPoint($x, $y - 1), // точка сверху
                $this->getPoint($x, $y + 1), // точка снизу
                $this->getPoint($x - 1, $y), // точка слева
                $this->getPoint($x + 1, $y) // точка справа
            );
            foreach ($nearbyPoints as $nearbyPoint) {
                $nearbyType = $this->getPointType($nearbyPoint);
                if ($this->typeIsAllowed($nearbyType)) {
                    $nextPoints[] = $nearbyPoint;
                }
            }
        }

        return $nextPoints;
    }

    /**
     * Проходим маршрутом назад из точки финиша в точку старта и помечаем точки пути
     * Таким образом получаем самый короткий путь
     * @param array $point
     * @throws Exception ошибка о том, что невозможно пройти из точки старта в точку финиша
     */
    private function getPath(array $point)
    {
        if (!$this->_ready) {
            throw new Exception($this::ERROR_PATH);
        }

        $pointValue = $this->getPointValue($point);
        $pathPoint = array();
        foreach ($this->getNextPoints($point) as $nextPoint) {
            $type = $this->getPointType($nextPoint);
            if ($type === $this::TYPE_START) {
                // если пришли в точку старта, то завершаем работу функции
                return;
            }
            $value = $this->getPointValue($nextPoint);
            if ($value === null or $value >= $pointValue) {
                continue;
            }
            if (empty($pathPoint) or $value < $pointValue) {
                $this->setIsPath($nextPoint);
                $pathPoint = $nextPoint;
                break;
            }
        }
        $this->getPath($pathPoint);
    }

    /**
     * Проверяет возможен ли проход через точку типа $type
     * @param $type
     * @return bool
     */
    private function typeIsAllowed($type)
    {
        return $type !== null and in_array($type, array($this::TYPE_START, $this::TYPE_FINISH, $this::TYPE_PATH));
    }

    /**
     * Просчитываем все возможные пути прохода, пока не дойдем до точки финиша,
     * либо не зайдем в тупик. Выбираем самый короткий путь
     * @throws Exception
     */
    public function calculatePath()
    {
        // проходим от точки старта во все возможные стороны пока не дойдем до финиша
        // либо не зайдем в тупик
        $this->setValues(array($this->getStartPoint()));
        // идем обратным путем от точки финиша, каждый раз переходим на точку с меньшим значением шага
        // пока не дойдем до старта
        $this->getPath($this->getFinishPoint());
    }

    /**
     * Проверяет является ли точка точкой пути.
     * @param array $point
     * @return bool
     */
    private function pointIsPath(array $point)
    {
        return (isset($point['is_path'])) ? (bool)$point['is_path'] : false;
    }

    /**
     * Назначаем точку точкой пути
     * @param array $point
     */
    private function setIsPath(array $point)
    {
        $x = $this->getPointCoordinate($point, 'x');
        $y = $this->getPointCoordinate($point, 'y');
        if (isset($this->_array[$x][$y])) {
            $this->_array[$x][$y]['is_path'] = true;
        }
    }

    /**
     * Вывод лабиринта в виде таблицы
     * @return string
     */
    public function display()
    {
        $html = '<div style="display: inline-block; padding: 10px;">
        <table cellspacing="0" border="1" style="margin: 20px auto; display: block;">';
        for ($y = -1; $y < $this->_sizeY; $y++) {
            $html .= '<tr>';
            for ($x = 0; $x < $this->_sizeX; $x++) {
                if ($y === -1) {
                    if ($x === 0) {
                        $html .= '<td width="50px" height="50px" align="center"></td>';
                    }
                    $html .= '<td width="50px" height="50px" align="center">' . $x . '</td>';
                } else {
                    $point = $this->getPoint($x, $y);
                    $type = $this->getPointType($point);
                    $isPath = $this->pointIsPath($point);
                    $color = ($type === $this::TYPE_BLOCK) ? 'black' : '#CDFFF0';
                    if ($x === 0) {
                        $html .= '<td width="50px" height="50px" align="center">' . $y . '</td>';
                    }
                    $html .= '<td width="50px" height="50px" align="center" style="background-color: ' . $color . '">';
                    if ($type === $this::TYPE_START) {
                        $html .= $this::SYMBOL_START;
                    } elseif ($type === $this::TYPE_FINISH) {
                        $html .= $this::SYMBOL_FINISH;
                    } elseif ($isPath) {
                        $html .= '*';
                    }
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table></div>';

        return $html;
    }
}
