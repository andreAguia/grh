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
                if ($pessoal->get_licencaPericia($tipo) == "Sim") {
                    
                    # Monta o arquivo
                    $arquivo = PASTA_BIM . "{$idLicenca}.pdf";

                    # Verifica se ele existe
                    if (file_exists($arquivo)) {

                        $botao = new BotaoGrafico();
                        $botao->set_label($this->getnumBim($idLicenca));
                        $botao->set_url($arquivo);                        
                        $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                        $botao->set_title("Exibe o Bim arquivado");
                        $botao->set_target("_blank");
                        $botao->show();
                    } else {

                        $botao = new BotaoGrafico();
                        $botao->set_label($this->getnumBim($idLicenca));
                        $botao->set_url("?fase=uploadBim&id={$idLicenca}&tipo=novo");
                        $botao->set_imagem(PASTA_FIGURAS . 'upload.png', 20, 20);
                        $botao->set_title("Faz o Upload do Bim");
                        $botao->show();
                    }
                } else {
                    return "---";
                }
            } else {
                return "---";
            }
        }
    }

###########################################################

    /**
     * Método getnumBim
     * informa o número do Bim
     * 
     * @param	integer $idLicenca id da licença
     */
    function getnumBim($idLicenca) {
        $select = "SELECT num_Bim
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################
}
