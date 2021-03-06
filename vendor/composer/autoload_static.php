<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbf5b84afc74a6c60bbb9cdf351399088
{
    public static $files = array (
        '253c157292f75eb38082b5acb06f3f01' => __DIR__ . '/..' . '/nikic/fast-route/src/functions.php',
        '7b11c4dc42b3b3023073cb14e519683c' => __DIR__ . '/..' . '/ralouphie/getallheaders/src/getallheaders.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tuupola\\Middleware\\' => 19,
            'Tuupola\\Http\\Factory\\' => 21,
        ),
        'S' => 
        array (
            'Slim\\Psr7\\' => 10,
            'Slim\\' => 5,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Psr\\Http\\Server\\' => 16,
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Container\\' => 14,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
            'Fig\\Http\\Message\\' => 17,
            'FastRoute\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tuupola\\Middleware\\' => 
        array (
            0 => __DIR__ . '/..' . '/tuupola/callable-handler/src',
            1 => __DIR__ . '/..' . '/tuupola/slim-basic-auth/src',
            2 => __DIR__ . '/..' . '/tuupola/slim-jwt-auth/src',
        ),
        'Tuupola\\Http\\Factory\\' => 
        array (
            0 => __DIR__ . '/..' . '/tuupola/http-factory/src',
        ),
        'Slim\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/slim/psr7/src',
        ),
        'Slim\\' => 
        array (
            0 => __DIR__ . '/..' . '/slim/slim/Slim',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Http\\Server\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-server-handler/src',
            1 => __DIR__ . '/..' . '/psr/http-server-middleware/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-factory/src',
            1 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'Fig\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/fig/http-message-util/src',
        ),
        'FastRoute\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/fast-route/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Pimple' => 
            array (
                0 => __DIR__ . '/..' . '/pimple/pimple/src',
            ),
        ),
    );

    public static $classMap = array (
        'FileMaker' => __DIR__ . '/../..' . '/src/lib/FileMaker.php',
        'FileMaker_Command' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command.php',
        'FileMaker_Command_Add' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/Add.php',
        'FileMaker_Command_Add_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/AddImpl.php',
        'FileMaker_Command_CompoundFind' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/CompoundFind.php',
        'FileMaker_Command_CompoundFind_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/CompoundFindImpl.php',
        'FileMaker_Command_Delete' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/Delete.php',
        'FileMaker_Command_Delete_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/DeleteImpl.php',
        'FileMaker_Command_Duplicate' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/Duplicate.php',
        'FileMaker_Command_Duplicate_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/DuplicateImpl.php',
        'FileMaker_Command_Edit' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/Edit.php',
        'FileMaker_Command_Edit_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/EditImpl.php',
        'FileMaker_Command_Find' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/Find.php',
        'FileMaker_Command_FindAll' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/FindAll.php',
        'FileMaker_Command_FindAll_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/FindAllImpl.php',
        'FileMaker_Command_FindAny' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/FindAny.php',
        'FileMaker_Command_FindAny_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/FindAnyImpl.php',
        'FileMaker_Command_FindRequest' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/FindRequest.php',
        'FileMaker_Command_FindRequest_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/FindRequestImpl.php',
        'FileMaker_Command_Find_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/FindImpl.php',
        'FileMaker_Command_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/CommandImpl.php',
        'FileMaker_Command_PerformScript' => __DIR__ . '/../..' . '/src/lib/FileMaker/Command/PerformScript.php',
        'FileMaker_Command_PerformScript_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Command/PerformScriptImpl.php',
        'FileMaker_Error' => __DIR__ . '/../..' . '/src/lib/FileMaker/Error.php',
        'FileMaker_Error_Validation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Error/Validation.php',
        'FileMaker_Field' => __DIR__ . '/../..' . '/src/lib/FileMaker/Field.php',
        'FileMaker_Field_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/FieldImpl.php',
        'FileMaker_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/FileMakerImpl.php',
        'FileMaker_Layout' => __DIR__ . '/../..' . '/src/lib/FileMaker/Layout.php',
        'FileMaker_Layout_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/LayoutImpl.php',
        'FileMaker_Parser_FMPXMLLAYOUT' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Parser/FMPXMLLAYOUT.php',
        'FileMaker_Parser_FMResultSet' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/Parser/FMResultSet.php',
        'FileMaker_Record' => __DIR__ . '/../..' . '/src/lib/FileMaker/Record.php',
        'FileMaker_Record_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/RecordImpl.php',
        'FileMaker_RelatedSet' => __DIR__ . '/../..' . '/src/lib/FileMaker/RelatedSet.php',
        'FileMaker_RelatedSet_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/RelatedSetImpl.php',
        'FileMaker_Result' => __DIR__ . '/../..' . '/src/lib/FileMaker/Result.php',
        'FileMaker_Result_Implementation' => __DIR__ . '/../..' . '/src/lib/FileMaker/Implementation/ResultImpl.php',
        'PEAR' => __DIR__ . '/../..' . '/src/lib/FileMaker/PEAR.php',
        'PEAR_Error' => __DIR__ . '/../..' . '/src/lib/FileMaker/PEAR.php',
        'config\\dbconnection' => __DIR__ . '/../..' . '/src/config/connection.php',
        'config\\jwt' => __DIR__ . '/../..' . '/src/config/jwt.php',
        'jobs\\jobsclass' => __DIR__ . '/../..' . '/jobs/jobs.php',
        'media\\media' => __DIR__ . '/../..' . '/media/mediafunctions.php',
        'users\\userclass' => __DIR__ . '/../..' . '/users/users.php',
        'valuelists\\valueclass' => __DIR__ . '/../..' . '/valuelists/valuelists.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbf5b84afc74a6c60bbb9cdf351399088::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbf5b84afc74a6c60bbb9cdf351399088::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitbf5b84afc74a6c60bbb9cdf351399088::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitbf5b84afc74a6c60bbb9cdf351399088::$classMap;

        }, null, ClassLoader::class);
    }
}
