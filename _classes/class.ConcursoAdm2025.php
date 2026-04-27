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
         * Informa as vagas gerais
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

    function exibe_listaCandidatosCargo($cargoConcurso = null, $cota = "Ac", $exibeErros = false) {
        /*
         * Exibe os candidatos em um cargo específico
         */

        # Classes
        $concurso = new Concurso();
        $pessoal = new Pessoal();

        # Pega o idConcurso
        $idConcurso = $this->get_idConcurso();

        # Cadastro de reserva
        $cadReserva = 5;

        # Define os dados de acordo com as cotas
        switch ($cota) {
            // Ampla Concorrência
            case "Ac":
                $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $cargoConcurso);
                $campo = "classifAc";
                $campoVaga = "vagas";
                $subtitulo = "Ampla Concorrência";
                break;

            // Pcd
            case "Pcd":
                $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $cargoConcurso);
                $campo = "classifPcd";
                $campoVaga = "vagasPcd";
                $subtitulo = "Cota: PCD";
                break;

            // Negros e Indígenas
            case "Ni":
                $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $cargoConcurso);
                $campo = "classifNi";
                $campoVaga = "vagasNi";
                $subtitulo = "Cota: Negros e Indígenas";
                break;

            // Hipossuficiente Econômico
            case "Hipo":
                $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $cargoConcurso);
                $campo = "classifHipo";
                $campoVaga = "vagasHipo";
                $subtitulo = "Cota: Hipossuficiente Econômico";
                break;
        }

        # Define o cadastro de reserva, quando se tem o número de vagas
        if (empty($numeroVagas)) {
            $numeroVagas = null;
            $cadastroReserva = null;
            $foraCadastro = null;
            if ($cargoConcurso <> "*") {
                $subtitulo .= " - SEM Vagas";
            }
        } else {
            $cadastroReserva = $cadReserva * $numeroVagas;
            $foraCadastro = $numeroVagas + $cadastroReserva;
            $subtitulo .= " - {$numeroVagas} Vaga(s)";
        }


        # Monta o select
        $select = "SELECT {$campo},
                              if({$campo} <= tbconcursovagadetalhada.{$campoVaga},'Vaga',if({$campo} BETWEEN tbconcursovagadetalhada.{$campoVaga} AND tbconcursovagadetalhada.{$campoVaga}*{$cadReserva}+tbconcursovagadetalhada.{$campoVaga},'Cadastro de Reserva','---')),
                              inscricao,
                              idCandidato,
                              dtNascimento,
                              idCandidato,
                              CONVERT(notaFinal, DECIMAL(10,2)),
                              idCandidato,
                              idCandidato,
                              idCandidato
                         FROM tbcandidato JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                        WHERE tbcandidato.idConcurso = {$idConcurso}
                          AND ({$campo} <> 0 AND {$campo} IS NOT NULL)
                          AND cargo = '{$cargoConcurso}'";

        # Cota
        if ($cota <> "Ac") {
            $select .= " AND {$campo} IS NOT NULL";
        }

        # Ordenação
        $select .= " ORDER BY {$campo}";

        # Pega os dados
        $row = $pessoal->select($select);

        # Verifica se tem erro na classificação
        if ($exibeErros) {
            $inicio = 0;
            $problemas = 0;

            # Cria um laço
            foreach ($row as $key => $linha) {
                # incrementa o início
                $inicio++;

                # Verifica se classificação é igual ao início
                if ($linha[$campo] <> $inicio) {
                    $row[$key][0] = "<span class='label warnning' title='Númedo Errado!'>{$linha[$campo]} - {$inicio}</span>";
                    $problemas++;

//                    # acerta a listagem - Retirei opis ja resolveu
//                    # Caso apareça algum outro erro eu reativo
//                    $sql = "UPDATE tbcandidato SET {$campo} = {$inicio}
//                             WHERE idCandidato = {$linha['idCandidato']}";
//                    $pessoal->update($sql);                   
//                    
                }
            }

            # Informa se houve problemas
            if ($problemas > 0) {
                callout("{$problemas} Problemas encontrados na classificação deste cargo");
            }
        }

        # tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Candidatos Aprovados");
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_conteudo($row);
        $tabela->set_label(["#", "Situação", "Inscrição", "Candidato", "Nascimento", "Classificação", "Nota Final", "Ofício", "Obs", "Editar"]);
        $tabela->set_width([5, 10, 10, 30, 10, 10, 10, 10]);
        $tabela->set_align(["center", "center", "center", "left", "center"]);
        $tabela->set_funcao(["trataNulo", null, null, "plm", "date_to_php"]);

        $tabela->set_classe([null, null, null, "CandidatoAdm2025", null, "CandidatoAdm2025", null, "CandidatoAdm2025", "CandidatoAdm2025"]);
        $tabela->set_metodo([null, null, null, "get_nomeECargoELotacaoESituacao", null, "exibeClassific", null, "exibeNumOficio", "exibeObs"]);

        # Botão Editar
        $botao = new Link(null, "?fase=editaCandidato&id=", 'Acessa os dados do Candidato');
        $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

        # Coloca o objeto link na tabela			
        $tabela->set_link([null, null, null, null, null, null, null, null, null, $botao]);

        $tabela->set_rowspan(1);
        $tabela->set_grupoCorColuna(1);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 1,
                'valor' => 'Vaga',
                'operador' => '=',
                'id' => "naVaga"),
            array('coluna' => 1,
                'valor' => 'Cadastro de Reserva',
                'operador' => '=',
                'id' => "reserva")));

        $tabela->set_mensagemPosTabela("O Cadastro de Reserva é de 5 vezes o número de vagas");
        $tabela->show();
    }
}
