<?php

namespace app\models;

use InvalidArgumentException;

class MathUtils
{
    const SCALE = 6; // Precisión por defecto: 6 decimales

    public static function add($a, $b, $scale = self::SCALE)
    {
        return bcadd(self::str($a), self::str($b), $scale);
    }

    public static function resta($a, $b, $scale = self::SCALE)
    {
        return bcsub(self::str($a), self::str($b), $scale);
    }

    public static function mul($a, $b, $scale = self::SCALE)
    {
        return bcmul(self::str($a), self::str($b), $scale);
    }

    public static function div($a, $b, $scale = self::SCALE)
    {
        if ((float)$b == 0.0) {
            throw new InvalidArgumentException("División por cero");
        }
        return bcdiv(self::str($a), self::str($b), $scale);
    }

    public static function comp($a, $b, $scale = self::SCALE)
    {
        return bccomp(self::str($a), self::str($b), $scale);
    }

    public static function sumMany(array $valores, $scale = self::SCALE)
    {
        $resultado = '0';
        foreach ($valores as $valor) {
            $resultado = self::add($resultado, $valor, $scale);
        }
        return $resultado;
    }

    public static function round($number, $precision = 2)
    {
        $number = self::str($number);
        $factor = bcpow('10', $precision + 1); // Ej: 10^3 = 1000 para precision 2
        $temp = bcmul($number, $factor, 0);   // Multiplicamos y truncamos

        // Extraer el último dígito
        $lastDigit = (int)substr($temp, -1);
        $rounded = bcdiv(substr($temp, 0, -1) + ($lastDigit >= 5 ? 1 : 0), bcpow('10', $precision), $precision);

        return $rounded;
    }

    // Convierte cualquier valor numérico a string
    private static function str($value)
    {
        return is_string($value) ? $value : number_format((float)$value, self::SCALE, '.', '');
    }

    public  static function fixNum($v) {
    // convierte a float y redondea a 6 decimales
        return (float) round((float) $v, 6);
    }
}
