<?php
//For working with global system objects
class Objects {
	public	$Loaded				= [],		//Array with list of loaded objects, and information about amount of used memory
											//после их создания, и длительностью содания
			$unload_priority	= [
				'Page',
				'User',
				'Config',
				'Key',
				'db',
				'L',
				'Text',
				'Cache',
				'Core',
				'Storage',
				'Error'
			];
	private	$List				= [];
	//Добавление в список объектов для их разрушения по окончанию работы
	function add ($name) {
		$this->List[$name] = $name;
	}
	/**
	 * @param array|string     $class
	 * @param bool             $custom_name
	 *
	 * @return bool|object
	 */
	function load ($class, $custom_name = false) {
		if (empty($class)) {
			return false;
		} elseif (!defined('STOP') && !is_array($class)) {
			$loader = false;
			if (substr($class, 0, 1) == '_') {
				$class	= substr($class, 1);
				$loader	= true;
			}
			if ($loader || class_exists($class)) {
				//Используем заданное имя для объекта
				if ($custom_name !== false) {
					global $$custom_name;
					if (!is_object($$custom_name) || $$custom_name instanceof Loader) {
						if ($loader) {
							$$custom_name				= new Loader($custom_name, $class);
						} else {
							$this->List[$custom_name]	= $custom_name;
							$$custom_name				= new $class();
							$this->Loaded[$custom_name]	= [microtime(true), memory_get_usage()];
						}
					}
					return $$custom_name;
				//Для имени объекта используем название класса
				} else {
					global $$class;
					if (!is_object($$class) || $$class instanceof Loader) {
						if ($loader) {
							$$class					= new Loader($class, $class);
						} else {
							$this->List[$class]		= $class;
							$$class					= new $class();
							$this->Loaded[$class]	= [microtime(true), memory_get_usage()];
						}
					}
					return $$class;
				}
			} else {
				global $L;
				trigger_error($L->class.' '.h::b($class).' '.$L->not_exists, E_USER_ERROR);
				return false;
			}
		} elseif (!defined('STOP') && is_array($class)) {
			foreach ($class as $c) {
				if (is_array($c)) {
					$this->load($c[0], isset($c[1]) ? $c[1] : false);
				} else {
					$this->load($c);
				}
			}
		}
		return false;
	}
	//Метод уничтожения объектов
	function unload ($class) {
		if (is_array($class)) {
			foreach ($class as $c) {
				$this->unload($c);
			}
		} else {
			global $$class;
			unset($this->List[$class]);
			method_exists($$class, '__finish') && $$class->__finish();
			$$class = null;
			unset($GLOBALS[$class]);
		}
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
	//При уничтожении этого объекта уничтожаются все зарегистрированные глобальные объекты,
	//проводится зачистка работы и корректное завершение
	function __finish () {
		if (isset($this->List['Index'])) {
			$this->unload('Index');
		}
		foreach ($this->List as $class) {
			if (!in_array($class, $this->unload_priority)) {
				$this->unload($class);
			}
		}
		foreach ($this->unload_priority as $class) {
			if (isset($this->List[$class])) {
				$this->unload($class);
			}
		}
		exit;
	}
}