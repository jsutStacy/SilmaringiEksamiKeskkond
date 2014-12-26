<?php
namespace Application\Validator;

use Zend\Validator\AbstractValidator;

class IdCode extends AbstractValidator
{
    const NOT_VALID = 'not_valid';

    protected $messageTemplates = array(
        self::NOT_VALID => "Vale isikukood!"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        $isValid = true;

        if (!$this->validate($value)) {
            $this->error(self::NOT_VALID);
            $isValid = false;
        }

        return $isValid;
    }

    private function validate($kood)
    {
        if (strlen($kood) != 11 || !is_numeric($kood)) {
            return false;
        }

        if ($kood == '12345678910') {
            return false;
        }

        $parts = unpack("a1sugu/a2aasta/a2kuu/a2paev/a3jrk/a1chk", $kood);

        $s1 = $s2 = 0; //mõlemad summad alguses 0-ga võrdseks!
        $k1 = 1; //esimese summa kordaja algab 1-st
        $k2 = 3; //teise summa kordaja algab 3-st
        for ($i = 0; $i < strlen($kood) - 1; $i++) { //leiame IK kõikide numbrite kontrollsumma, jättes välja ainult viimase numbri!
            $s1 += $kood[$i] * $k1; //Arvutame esimese summa
            $s2 += $kood[$i] * $k2; //Arvutame teise summa
            $k1 = ($k1 == 9) ? 1 : $k1 + 1; //Suurendame esimese summa kordajat, kui on võrdne 9 siis kordaja võrdseks 1-ga
            $k2 = ($k2 == 9) ? 1 : $k2 + 1; //Suurendame teise summa kordajat, kui on võrdne 9 siis kordaja võrdseks 1-ga
        }
        if (($s1 % 11) < 10) { //Kui esimese summa 11-ga jagamise jääk on väiksem, kui 10 siis see ongi kontrollsumma
            $jaak = $s1 % 11;
        } elseif (($s2 % 11) < 10) { //Kui teise summa 11-ga jagamise jääk on väiksem, kui 10 siis see ongi kontrollsumma
            $jaak = $s2 % 11;
        } else { //muul juhul on kontrollsummaks 0 (null)
            $jaak = 0;
        }

        if ($parts["chk"] != $jaak) {
            return false;
        };

        return true;
    }
}

?>