<?php

use Stevebauman\Purify\Definitions\Html5Definition;

return [

    'default' => 'blog',

    'configs' => [

        'default' => [
            'Core.Encoding' => 'utf-8',
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,b,u,strong,i,em,s,del,a[href|title],ul,ol,li,p[style],br,span,img[width|height|alt|src],blockquote',
            'HTML.ForbiddenElements' => '',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => false,
        ],

        'blog' => [
            'Core.Encoding' => 'utf-8',
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,b,u,strong,i,em,s,del,ins,sub,sup,mark,a[href|title|target|rel],ul,ol,li,p[style],br,span[style],div[style],img[src|alt|title|width|height|style],table[style],caption,colgroup,col,tbody,thead,tfoot,tr,th[colspan|rowspan|style],td[colspan|rowspan|style],blockquote,pre,code,hr,figure,figcaption',
            'HTML.ForbiddenElements' => 'script,style,iframe,object,embed,form,input,textarea,select,button,label',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,padding-right,padding-top,padding-bottom,padding,margin-left,margin-right,margin-top,margin-bottom,margin,color,background-color,text-align,vertical-align,width,height,max-width,max-height,border,border-collapse,border-spacing,float,list-style-type,line-height,letter-spacing,white-space',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ],
    ],

    'definitions' => Html5Definition::class,

    'css-definitions' => null,

    'serializer' => [
        'driver' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
        'cache' => \Stevebauman\Purify\Cache\CacheDefinitionCache::class,
    ],
];
