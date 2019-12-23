<?php

return [
    'BillingBase' => [
        'cdr_offset' 		=> "ADVERTENCIA: incrementar esto mientras se tiene datos de Acuerdos ocasiona sobrescribir CDRs la siguiente ejecucion - Este seguro de guardar/renombrar el historial!\n\nEjemplo: Asignar a 1 si los Registros de Datos de Llamada de Junio, pertenecen a las Facturas de Julio, 0 si este es del mismo mes, 2 si RDLs de Enero pertenecen a las Facturas de Marzo.",
        'cdr_retention' 	=> 'Meses que Registros de Datos de Llamada Months that Call Data Records may/have to be kept save',
        'extra_charge' 		=> 'Beneficio adicional al precio de compra. Solo cuando no es calculado mediante el proveedor!',
        'fluid_dates' 		=> 'Marque esta casilla si quiere aniadir tarifas con fechas de inicio y/o plazo dudosas. Si se marcaron dos nuevos casilleros (Valido desde, Valido hasta) aparecera en la pagina editar/crear Articulo. Revise sus mensajes de ayuda para explicaciones adicionales!',
        'invoiceNrStart' 	=> 'Contador de Cifras de Factura empieza cada nuevo anio con esta cifra',
        'ItemTermination'	=> 'Permitir a los Clientes solo cancelar productos reservados el ultimo dia del mes',
        'MandateRef'		=> "Un Formulario puede ser construido con columnas SQL de las tablas contrato/mandato - posibles campos: \n",
        'showAGs' 			=> 'Adiciona una lista seleccionada con personas contactadas a la pagina de contrato. La lista tiene que ser almacenada en un directorio Alamacenamiento apropiado - revise el codigo fuente!',
        'SplitSEPA'			=> 'Las transferencias de Sepa se dividen en diferentes archivos XML dependiendo de su tipo de transferencia',
    ],
    'sepaAccount' => [
        'invoiceHeadline'   => 'Remplaza el Encabezado en Facturas creadas para este Centro de Coste',
        'invoiceText'       => 'El Texto de los cuatro Campos-\'Texto de Factura\' independientes, es automaticamente escogido dependiendo del cargo total y del Mandado SEPA, ademas es establecido en la Factura para el Cliente apropiada. Es posible de usar todos los datos de campo primarios de la Clase Factura como referente en la forma de {fieldname} para construir un tipo de plantilla. Estos son reemplazados por el valor actual de la Factura.',
    ],
    'texTemplate'           => 'Plantilla TeX',
];
