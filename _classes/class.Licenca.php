<?php

class Licenca {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function exibeNome($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome, lei
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        pLista(
                $licenca[0],
                $licenca[1]
        );
    }

##############################################################

    public function getNome($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        return $licenca[0];
    }

##############################################################


    public function exibeNomeSimples($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (vazio($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        return $licenca[0];
    }

##########################################################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tblicenca
                    WHERE idLicenca = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

##########################################################################################

    public function exibePeriodo($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT dtInicial,
                          numdias,
                          ADDDATE(dtInicial,numDias-1) as dtTermino
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $licenca = $servidor->select($select, false);

        echo "Início: ", date_to_php($licenca['dtInicial']);
        br();
        echo "Período: ", $licenca['numdias'], " dias";
        br();
        echo "Término: ", date_to_php($licenca['dtTermino']);
    }

##########################################################################################

    public function analisaTermino($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT alta,
                          TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) as faltam
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $licenca = $servidor->select($select, false);

        # Verifica se tem ou não ALTA
        if ($licenca['alta'] == 1) {
            if ($licenca['faltam'] > 0) {
                echo "Falta(m) " . abs($licenca['faltam']) . " dias<br/>para terminar COM ALTA";
            } else {
                echo "Terminado COM ALTA<br/>há " . abs($licenca['faltam']) . " dias";
            }
        } else {
            if ($licenca['faltam'] > 0) {
                echo "Falta(m) " . abs($licenca['faltam']) . " dias<br/>para terminar<br/>";
                label("SEM ALTA");
            } else {
                label("Licença em aberto");
                echo "<br>há " . abs($licenca['faltam']) . " dias";
            }
        }
    }

########################################################### 
}
