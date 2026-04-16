<?php

class ConcursoAdm2025 {

    /**
     * Abriga as várias rotina específicasd do Concurso Administrativo de 2025
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function get_arrayCotas() {
        /**
         * Fornece e padroniza o array com as cotas
         */
        $array = [
            ["Ac", "Ampla Concorrência"],
            ["Pcd", "PCD"],
            ["Ni", "Negros e Indígenas"],
            ["Hipo", "Hipossuficiente Econômico"],
        ];
        return $array;
    }

    ###########################################################

    function get_idConcurso() {
        /**
         * informa o idConcurso 
         */
        return 96;
    }

    ###########################################################

    function get_obsCargo($cargoConcurso = null) {
        /**
         * Informa a obs do cargo
         */
        if (empty($cargoConcurso)) {
            return null;
        } else {
            $select = "SELECT obs 
                         FROM tbconcursovagadetalhada
                        WHERE cargoConcurso = '{$cargoConcurso}'";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row["obs"];
        }
    }

    ###########################################################

    function get_situacaoClassifVaga($classif = null, $cota = "Ac", $cargoConcurso = null) {
        /**
         * Informa se a classificação informada, da cota informada para o cargo informado está em que situação
         * 
         * Retorna: VA - Quando está na vaga
         *          CR - Cadastro de Reserva
         *          Null - Quando está fora do cadastro de Reserva
         */
        # Define o id do concurso
        $idConcurso = $this->get_idConcurso();

        # Verifica se veio a classificação
        if (empty($classif)) {
            return null;
        }

        # Verifica se veio a cota
        if (empty($cota)) {
            return null;
        }

        # Verifica se veio o cargo
        if (empty($cargoConcurso)) {
            return null;
        }

        # Acessa a classe de concurso
        $concursoClasse = new Concurso();

        # Pega o número de vagas
        if ($cota == "Ac") {
            $numVagas = $concursoClasse->get_numVagasAcAprovadas($idConcurso, $cargoConcurso);
        }

        if ($cota == "Pcd") {
            $numVagas = $concursoClasse->get_numVagasPcdAprovadas($idConcurso, $cargoConcurso);
        }

        if ($cota == "Ni") {
            $numVagas = $concursoClasse->get_numVagasNiAprovadas($idConcurso, $cargoConcurso);
        }

        if ($cota == "Hipo") {
            $numVagas = $concursoClasse->get_numVagasHipoAprovadas($idConcurso, $cargoConcurso);
        }

        # Calcula o cadastro de reserva
        $cr = (5 * $numVagas) + $numVagas;

        # Compara com a classificação
        if ($classif <= $numVagas) {
            return "V";
            #return " <span class='label success' title='Dentro do Número de Vagas'>V</span>";
        }

        if ($classif > $numVagas AND $classif <= $cr) {
            return "R";
            #return " <span class='label warning' title='No Cadastro de Reserva'>R</span>";
        }
    }

    ###########################################################

    function get_vagasGeral($cargoConcurso = null) {
        /**
         * Informa a obs do cargo
         */
        if (empty($cargoConcurso)) {
            return null;
        } else {
            # Cria o select
            $select = "SELECT * 
                         FROM tbconcursovagadetalhada
                        WHERE cargoConcurso = '{$cargoConcurso}'";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Define a variavel de retorno
            $retorno = null;

            # Verifica as vagas AC
            $retorno .= "AC - {$row['vagas']}";

            # Verifica as vagas PCD
            if (!empty($row['vagasPcd'])) {
                $retorno .= "<br/><hr id='geral'/>";
                $retorno .= "Pcd - {$row['vagasPcd']}";
            }

            # Verifica as vagas Ni
            if (!empty($row['vagasNi'])) {
                $retorno .= "<br/><hr id='geral'/>";
                $retorno .= "Ni - {$row['vagasNi']}";
            }

            # Verifica as vagas Hipo
            if (!empty($row['vagasHipo'])) {
                $retorno .= "<br/><hr id='geral'/>";
                $retorno .= "Hipo - {$row['vagasHipo']}";
            }

            return $retorno;
        }
    }

    ###########################################################
}
