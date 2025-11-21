<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tipificacion;

class TipificacionesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Cancelo deuda',                 'CONTACTO DIRECTO',   '1 = CD+',          0],
            ['Pago realizado',                'CONTACTO DIRECTO',   '1 = CD+',          1],
            ['PROMESA DE PAGO',               'CONTACTO DIRECTO',   '1 = CD+',          2],
            ['Compromiso de pago',            'CONTACTO DIRECTO',   '1 = CD+',          2],
            ['Posible pago',                  'CONTACTO DIRECTO',   '1 = CD+',          3],
            ['Recordatorio de pago',          'CONTACTO DIRECTO',   '1 = CD+',          4],
            ['Cuenta refinanciada',           'CONTACTO DIRECTO',   '1 = CD+',          5],
            ['En negociacion',                'CONTACTO DIRECTO',   '1 = CD+',          6],
            ['Contacto sin compromiso',       'CONTACTO DIRECTO',   '1 = CD+',          7],
            ['Dificultad de pago',            'CONTACTO DIRECTO',   '1 = CD+',          8],
            ['Insolvente',                    'CONTACTO DIRECTO',   '1 = CD+',          9],
            ['Mensaje a conyuge',             'CONTACTO INDIRECTO', '1 = CD+',          10],
            ['ABANDON',                       'NO CONTACTO',        '3A = NC+ ABAND',   11],
            ['IVR Contestada',                'NO CONTACTO',        '3A = NC+ ABAND',   12],
            ['ANSWERED',                      'NO CONTACTO',        '3A = NC+ ABAND',   13],
            ['Sin contacto',                  'NO CONTACTO',        '3B = NC+',         14],
            ['No contestan',                  'NO CONTACTO',        '3B = NC+',         15],
            ['NO ANSWER',                     'NO CONTACTO',        '3B = NC+',         16],
            ['SMS recepcionado',              'SMS',                '3B = NC+',         17],
            ['Fallecido',                     'CONTACTO INDIRECTO', '4 = CI',           18],
            ['Mensaje a familiar',            'CONTACTO INDIRECTO', '4 = CI',           19],
            ['Mensaje a terceros',            'CONTACTO INDIRECTO', '4 = CI',           20],
            ['Cortan la llamada',             'NO CONTACTO',        '5 = CD-',          21],
            ['Renuente',                      'CONTACTO DIRECTO',   '5 = CD-',          22],
            ['Reclamo/Descargo',              'CONTACTO DIRECTO',   '5 = CD-',          23],
            ['BUSY',                          'NO CONTACTO',        '6 = NC-',          24],
            ['EXITWITHTIMEOUT',               'NO CONTACTO',        '6 = NC-',          25],
            ['Telefono Apagado',              'NO CONTACTO',        '6 = NC-',          26],
            ['CONGESTION',                    'NO CONTACTO',        '6 = NC-',          27],
            ['IVR Ocupado',                   'NO CONTACTO',        '6 = NC-',          28],
            ['IVR Sin Respuesta',             'NO CONTACTO',        '6 = NC-',          29],
            ['IVR Congestión',                'NO CONTACTO',        '6 = NC-',          30],
            ['FAILED',                        'NO CONTACTO',        '6 = NC-',          31],
            ['IVR Fallida',                   'NO CONTACTO',        '6 = NC-',          32],
            ['Nro. No pertenece',             'NO CONTACTO',        '6 = NC-',          33],
            ['Fds/Ne',                        'NO CONTACTO',        '6 = NC-',          34],
            ['SIN GESTION',                   'SIN GESTION',        '7 = SG+',          35],
            ['SIN GESTION - SIN TELEFONOS',   'SIN GESTION',        '8 = SG-',          36],
        ];

        $orden = 1;

        foreach ($rows as [$tip, $res, $mc, $peso]) {
            Tipificacion::create([
                'tipificacion' => $tip,
                'resultado'    => $res,
                'mc'           => $mc,
                'peso'         => $peso,
                'orden'        => $orden++,
            ]);
        }
    }
}
