<?php

class LicencaMedica {

    /**
     * Abriga as várias rotina referentes a licença médica de servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function exibeBim($idLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {            
            # Pega o tipo de licença
            $pessoal = new Pessoal();
            $tipo = $pessoal->get_tipoLicenca($idLicenca);

            if (!empty($tipo)) {
                # Verifica se esse tipo tem perícia
                if ($pessoal->get_licencaPericia($tipo)) {
                    return "---";
                } else {
                    return "---";
                }
            }else{
                return "---";
            }
        }
    }

##############################################################
}
