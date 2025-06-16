<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'A :attribute mezőt el kell fogadni.',
    'accepted_if' => 'A :attribute mezőt el kell fogadni, amikor :other :value.',
    'active_url' => 'A :attribute mező érvényes URL-t kell tartalmazzon.',
    'after' => 'A :attribute mező :date utáni dátumot kell tartalmazzon.',
    'after_or_equal' => 'A :attribute mező :date utáni vagy azzal egyenlő dátumot kell tartalmazzon.',
    'alpha' => 'A :attribute mező csak betűket tartalmazhat.',
    'alpha_dash' => 'A :attribute mező csak betűket, számokat, kötőjeleket és aláhúzásokat tartalmazhat.',
    'alpha_num' => 'A :attribute mező csak betűket és számokat tartalmazhat.',
    'array' => 'A :attribute mező tömbnek kell lennie.',
    'ascii' => 'A :attribute mező csak egybájtos alfanumerikus karaktereket és szimbólumokat tartalmazhat.',
    'before' => 'A :attribute mező :date előtti dátumot kell tartalmazzon.',
    'before_or_equal' => 'A :attribute mező :date előtti vagy azzal egyenlő dátumot kell tartalmazzon.',
    'between' => [
        'array' => 'A :attribute mezőnek :min és :max elem között kell lennie.',
        'file' => 'A :attribute mezőnek :min és :max kilobájt között kell lennie.',
        'numeric' => 'A :attribute mezőnek :min és :max között kell lennie.',
        'string' => 'A :attribute mezőnek :min és :max karakter között kell lennie.',
    ],
    'boolean' => 'A :attribute mező igaz vagy hamis értéket kell tartalmazzon.',
    'can' => 'A :attribute mező jogosulatlan értéket tartalmaz.',
    'confirmed' => 'A :attribute mező megerősítése nem egyezik.',
    'current_password' => 'A jelszó helytelen.',
    'date' => 'A :attribute mező érvényes dátumot kell tartalmazzon.',
    'date_equals' => 'A :attribute mező :date-vel egyenlő dátumot kell tartalmazzon.',
    'date_format' => 'A :attribute mező :format formátumnak kell megfeleljen.',
    'decimal' => 'A :attribute mezőnek :decimal tizedesjegyet kell tartalmaznia.',
    'declined' => 'A :attribute mezőt el kell utasítani.',
    'declined_if' => 'A :attribute mezőt el kell utasítani, amikor :other :value.',
    'different' => 'A :attribute mező és :other különbözőnek kell lennie.',
    'digits' => 'A :attribute mezőnek :digits számjegyet kell tartalmaznia.',
    'digits_between' => 'A :attribute mezőnek :min és :max számjegy között kell lennie.',
    'dimensions' => 'A :attribute mező érvénytelen kép dimenziókat tartalmaz.',
    'distinct' => 'A :attribute mező duplikált értéket tartalmaz.',
    'doesnt_end_with' => 'A :attribute mező nem végződhet a következők egyikével: :values.',
    'doesnt_start_with' => 'A :attribute mező nem kezdődhet a következők egyikével: :values.',
    'email' => 'A :attribute mező érvényes e-mail címet kell tartalmazzon.',
    'ends_with' => 'A :attribute mezőnek a következők egyikével kell végződnie: :values.',
    'enum' => 'A kiválasztott :attribute érvénytelen.',
    'exists' => 'A kiválasztott :attribute érvénytelen.',
    'file' => 'A :attribute mező fájlnak kell lennie.',
    'filled' => 'A :attribute mezőnek értéket kell tartalmaznia.',
    'gt' => [
        'array' => 'A :attribute mezőnek :value elemnél többet kell tartalmaznia.',
        'file' => 'A :attribute mezőnek :value kilobájtnál nagyobbnak kell lennie.',
        'numeric' => 'A :attribute mezőnek :value-nál nagyobbnak kell lennie.',
        'string' => 'A :attribute mezőnek :value karakternél hosszabbnak kell lennie.',
    ],
    'gte' => [
        'array' => 'A :attribute mezőnek :value elemet vagy többet kell tartalmaznia.',
        'file' => 'A :attribute mezőnek :value kilobájtnál nagyobbnak vagy egyenlőnek kell lennie.',
        'numeric' => 'A :attribute mezőnek :value-nál nagyobbnak vagy egyenlőnek kell lennie.',
        'string' => 'A :attribute mezőnek :value karakternél hosszabbnak vagy egyenlőnek kell lennie.',
    ],
    'image' => 'A :attribute mező képnek kell lennie.',
    'in' => 'A kiválasztott :attribute érvénytelen.',
    'in_array' => 'A :attribute mezőnek léteznie kell :other-ben.',
    'integer' => 'A :attribute mező egész számnak kell lennie.',
    'ip' => 'A :attribute mező érvényes IP címnek kell lennie.',
    'ipv4' => 'A :attribute mező érvényes IPv4 címnek kell lennie.',
    'ipv6' => 'A :attribute mező érvényes IPv6 címnek kell lennie.',
    'json' => 'A :attribute mező érvényes JSON karakterláncnak kell lennie.',
    'lowercase' => 'A :attribute mezőnek kisbetűsnek kell lennie.',
    'lt' => [
        'array' => 'A :attribute mezőnek :value elemnél kevesebbnek kell lennie.',
        'file' => 'A :attribute mezőnek :value kilobájtnál kisebbnek kell lennie.',
        'numeric' => 'A :attribute mezőnek :value-nál kisebbnek kell lennie.',
        'string' => 'A :attribute mezőnek :value karakternél rövidebbnek kell lennie.',
    ],
    'lte' => [
        'array' => 'A :attribute mező nem tartalmazhat :value elemnél többet.',
        'file' => 'A :attribute mezőnek :value kilobájtnál kisebbnek vagy egyenlőnek kell lennie.',
        'numeric' => 'A :attribute mezőnek :value-nál kisebbnek vagy egyenlőnek kell lennie.',
        'string' => 'A :attribute mezőnek :value karakternél rövidebbnek vagy egyenlőnek kell lennie.',
    ],
    'mac_address' => 'A :attribute mező érvényes MAC címnek kell lennie.',
    'max' => [
        'array' => 'A :attribute mező nem tartalmazhat :max elemnél többet.',
        'file' => 'A :attribute mező nem lehet :max kilobájtnál nagyobb.',
        'numeric' => 'A :attribute mező nem lehet :max-nál nagyobb.',
        'string' => 'A :attribute mező nem lehet :max karakternél hosszabb.',
    ],
    'max_digits' => 'A :attribute mező nem tartalmazhat :max számjegynél többet.',
    'mimes' => 'A :attribute mezőnek a következő típusú fájlnak kell lennie: :values.',
    'mimetypes' => 'A :attribute mezőnek a következő típusú fájlnak kell lennie: :values.',
    'min' => [
        'array' => 'A :attribute mezőnek legalább :min elemet kell tartalmaznia.',
        'file' => 'A :attribute mezőnek legalább :min kilobájtnak kell lennie.',
        'numeric' => 'A :attribute mezőnek legalább :min-nak kell lennie.',
        'string' => 'A :attribute mezőnek legalább :min karaktert kell tartalmaznia.',
    ],
    'min_digits' => 'A :attribute mezőnek legalább :min számjegyet kell tartalmaznia.',
    'missing' => 'A :attribute mezőnek hiányoznia kell.',
    'missing_if' => 'A :attribute mezőnek hiányoznia kell, amikor :other :value.',
    'missing_unless' => 'A :attribute mezőnek hiányoznia kell, kivéve ha :other :values-ben van.',
    'missing_with' => 'A :attribute mezőnek hiányoznia kell, amikor :values jelen van.',
    'missing_with_all' => 'A :attribute mezőnek hiányoznia kell, amikor :values jelen vannak.',
    'multiple_of' => 'A :attribute mezőnek :value többszörösének kell lennie.',
    'not_in' => 'A kiválasztott :attribute érvénytelen.',
    'not_regex' => 'A :attribute mező formátuma érvénytelen.',
    'numeric' => 'A :attribute mezőnek számnak kell lennie.',
    'password' => [
        'letters' => 'A :attribute mezőnek legalább egy betűt kell tartalmaznia.',
        'mixed' => 'A :attribute mezőnek legalább egy nagy- és egy kisbetűt kell tartalmaznia.',
        'numbers' => 'A :attribute mezőnek legalább egy számot kell tartalmaznia.',
        'symbols' => 'A :attribute mezőnek legalább egy szimbólumot kell tartalmaznia.',
        'uncompromised' => 'A megadott :attribute adatszivárgásban szerepelt. Kérjük, válasszon másik :attribute-t.',
    ],
    'present' => 'A :attribute mezőnek jelen kell lennie.',
    'prohibited' => 'A :attribute mező tiltott.',
    'prohibited_if' => 'A :attribute mező tiltott, amikor :other :value.',
    'prohibited_unless' => 'A :attribute mező tiltott, kivéve ha :other :values-ben van.',
    'prohibits' => 'A :attribute mező megtiltja :other jelenlétét.',
    'regex' => 'A :attribute mező formátuma érvénytelen.',
    'required' => 'A :attribute mező kötelező.',
    'required_array_keys' => 'A :attribute mezőnek tartalmaznia kell bejegyzéseket a következőkhöz: :values.',
    'required_if' => 'A :attribute mező kötelező, amikor :other :value.',
    'required_if_accepted' => 'A :attribute mező kötelező, amikor :other elfogadva.',
    'required_unless' => 'A :attribute mező kötelező, kivéve ha :other :values-ben van.',
    'required_with' => 'A :attribute mező kötelező, amikor :values jelen van.',
    'required_with_all' => 'A :attribute mező kötelező, amikor :values jelen vannak.',
    'required_without' => 'A :attribute mező kötelező, amikor :values nincs jelen.',
    'required_without_all' => 'A :attribute mező kötelező, amikor egyik :values sincs jelen.',
    'same' => 'A :attribute mezőnek egyeznie kell :other-rel.',
    'size' => [
        'array' => 'A :attribute mezőnek :size elemet kell tartalmaznia.',
        'file' => 'A :attribute mezőnek :size kilobájtnak kell lennie.',
        'numeric' => 'A :attribute mezőnek :size-nak kell lennie.',
        'string' => 'A :attribute mezőnek :size karaktert kell tartalmaznia.',
    ],
    'starts_with' => 'A :attribute mezőnek a következők egyikével kell kezdődnie: :values.',
    'string' => 'A :attribute mezőnek karakterláncnak kell lennie.',
    'timezone' => 'A :attribute mezőnek érvényes időzónának kell lennie.',
    'unique' => 'A :attribute már foglalt.',
    'uploaded' => 'A :attribute feltöltése sikertelen.',
    'uppercase' => 'A :attribute mezőnek nagybetűsnek kell lennie.',
    'url' => 'A :attribute mezőnek érvényes URL-nek kell lennie.',
    'ulid' => 'A :attribute mezőnek érvényes ULID-nak kell lennie.',
    'uuid' => 'A :attribute mezőnek érvényes UUID-nak kell lennie.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
