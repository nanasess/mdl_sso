<?php

abstract class AbstractEntity implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        $method = Inflector::classify($offset);

        return method_exists($this, $method)
            || method_exists($this, "get$method")
            || method_exists($this, "is$method")
            || method_exists($this, "has$method");
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetGet($offset)
    {
        $method = Inflector::classify($offset);

        if (method_exists($this, $method)) {
            return $this->$method();
        } elseif (method_exists($this, "get$method")) {
            return $this->{"get$method"}();
        } elseif (method_exists($this, "is$method")) {
            return $this->{"is$method"}();
        } elseif (method_exists($this, "has$method")) {
            return $this->{"has$method"}();
        }
    }

    public function offsetUnset($offset)
    {
    }

    /**
     * 引数の連想配列を元にプロパティを設定します.
     * DBから取り出した連想配列を, プロパティへ設定する際に使用します.
     *
     * @param array $arrProps プロパティの情報を格納した連想配列
     * @param \ReflectionClass $parentClass 親のクラス. 本メソッドの内部的に使用します.
     * @param string[] $excludeAttribute 除外したいフィールド名の配列
     */
    public function setPropertiesFromArray(array $arrProps, array $excludeAttribute = [], \ReflectionClass $parentClass = null)
    {
        $objReflect = null;
        if (is_object($parentClass)) {
            $objReflect = $parentClass;
        } else {
            $objReflect = new \ReflectionClass($this);
        }
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            $objProperty->setAccessible(true);
            $name = $objProperty->getName();
            if (in_array($name, $excludeAttribute) || !array_key_exists($name, $arrProps)) {
                continue;
            }
            $objProperty->setValue($this, $arrProps[$name]);
        }

        // 親クラスがある場合は再帰的にプロパティを取得
        $parentClass = $objReflect->getParentClass();
        if (is_object($parentClass)) {
            self::setPropertiesFromArray($arrProps, $excludeAttribute, $parentClass);
        }
    }

    /**
     * Convert to associative array.
     *
     * Symfony Serializer Component is expensive, and hard to implementation.
     * Use for encoder only.
     *
     * @param \ReflectionClass $parentClass parent class. Use internally of this method..
     * @param array $excludeAttribute Array of field names to exclusion.
     *
     * @return array
     */
    public function toArray(array $excludeAttribute = [], \ReflectionClass $parentClass = null)
    {
        $objReflect = null;
        if (is_object($parentClass)) {
            $objReflect = $parentClass;
        } else {
            $objReflect = new \ReflectionClass($this);
        }
        $arrProperties = $objReflect->getProperties();
        $arrResults = [];
        foreach ($arrProperties as $objProperty) {
            $objProperty->setAccessible(true);
            $name = $objProperty->getName();
            if (in_array($name, $excludeAttribute)) {
                continue;
            }
            $arrResults[$name] = $objProperty->getValue($this);
        }

        $parentClass = $objReflect->getParentClass();
        if (is_object($parentClass)) {
            $arrParents = self::toArray($excludeAttribute, $parentClass);
            if (!is_array($arrParents)) {
                $arrParents = [];
            }
            if (!is_array($arrResults)) {
                $arrResults = [];
            }
            $arrResults = array_merge($arrParents, $arrResults);
        }

        return $arrResults;
    }

    /**
     * コピー元のオブジェクトのフィールド名を指定して、同名のフィールドに値をコピー
     *
     * @param object $srcObject コピー元のオブジェクト
     * @param string[] $excludeAttribute 除外したいフィールド名の配列
     *
     * @return AbstractEntity
     */
    public function copyProperties($srcObject, array $excludeAttribute = [])
    {
        $this->setPropertiesFromArray($srcObject->toArray($excludeAttribute), $excludeAttribute);

        return $this;
    }
}
