<?php

class Petec {
    # Preenche as variáveis com os dados do Petec
    ###########################################################

    function get_arrayPetec($idMarcador = null) {

        # Gera um array com os dados do Petec
        # Seguindo a seguinte ordem
        # 0 - Portaria
        # 1 - Data do Certificado
        # 2 - Horas Exigidas
        # 3 - Data limite de Entrega
        # 4 - Nome do arquivo pdf da portaria
        # Verifica se foi preenchido
        if (is_null($idMarcador)) {
            return null;
        } else {
            switch ($idMarcador) {
                case 4 :
                    return ["418/25", "21/07/2025", 20, "10/03/2026", 74];
                    break;

                case 5 :
                    return ["473/25", "15/12/2025", 10, "10/03/2026", 75];
                    break;

                case 6 :
                    return ["481/25", "18/12/2025", 20, "30/06/2026", 76];
                    break;
            }
        }
    }

    ###########################################################

    function temPetec($idServidor, $idMarcador) {
        /**
         * Informa se o servidor tem certificados com esse marcador
         */
        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return false;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Select
            $select = "SELECT *
                         FROM tbformacao
                        WHERE (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select);
            $quantidade = $pessoal->count($select);

            if ($quantidade == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    ###########################################################

    function exibeQuadroPortariasPetec() {
        /**
         * Exibe um quadro com as regras das portarias
         */
        # Pega os ids dos marcadores Petec
        $formacao = new Formacao();
        $idMarcadoresPetec = $formacao->get_arrayMarcadores("Petec");

        # Monta o array
        foreach ($idMarcadoresPetec as $item) {

            # Pega os dados dessa portaria
            $dados = $this->get_arrayPetec($item[0]);

            # Monta o array
            $array[] = [$dados[0], $item[0], $dados[4]];
        }
        $tabela = new Tabela();

        $tabela->set_titulo("Dados das Portarias");
        $tabela->set_label(["Portaria", "Dados", "Pdf"]);
        $tabela->set_width([20, 60, 20]);
        $tabela->set_align(["center", "center"]);

        $tabela->set_classe([null, "petec", "petec"]);
        $tabela->set_metodo([null, "exibeDadosPortaria", "exibePdfPetec"]);

        $tabela->set_conteudo($array);

        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    function exibeQuadroInscritosPetec($lotacao = null) {
        /**
         * Exibe um quadro com 
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Label da Lotação
        if (is_numeric($lotacao)) {
            $labelLotação = $pessoal->get_nomeLotacao2($lotacao);
        } else { # senão é uma diretoria genérica
            $labelLotação = $lotacao;
        }

        # Monta o array
        # Petec1 - Inscritos
        $select1 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                       AND petec1 = "s"
                       AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select1 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select1 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row1 = $pessoal->select($select1, false);

        # Petec1 - Não Inscritos
        $select2 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND (petec1 is null OR petec1 != "s")
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select2 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row2 = $pessoal->select($select2, false);

        # Petec2 - Inscritos
        $select3 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND petec2 = "s"
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select3 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select3 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $row3 = $pessoal->select($select3, false);

        # Petec2 - Não Inscritos
        $select4 = 'SELECT count(idServidor)
                      FROM tbservidor JOIN tbperfil USING (idPerfil)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND situacao = 1
                           AND (petec2 is null OR petec2 != "s")
                           AND tbperfil.tipo <> "Outros"';

        # Verifica se tem filtro por lotação
        if ($lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($lotacao)) {
                $select4 .= " AND (tblotacao.idlotacao = {$lotacao})";
            } else { # senão é uma diretoria genérica
                $select4 .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }
        $row4 = $pessoal->select($select4, false);

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo([
            ["Inscritos", $row1[0], $row3[0]],
            ["NÃO Inscritos", $row2[0], $row4[0]],
        ]);
        $tabela->set_titulo("Inscrição de Servidores");
        $tabela->set_subtitulo($labelLotação);
        $tabela->set_label(["Servidores", "Portarias<br/>418/25 e 473/25", "Portaria<br/>481/25"]);
        $tabela->set_align(["left", "center", "center"]);
        $tabela->set_width([33, 33, 33]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio([1, 2]);
        $tabela->show();
    }

    ##############################################################

    public function exibePdfPetec($id = null) {

        # Verifica se o id foi informado
        if (empty($id)) {
            return "---";
        } else {

            # Monta o arquivo
            $arquivo = PASTA_DOCUMENTOS . "{$id}.pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                $botao = new BotaoGrafico();
                $botao->set_url($arquivo);
                $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $botao->set_title("Exibe o Pdf");
                $botao->set_target("_blank");
                $botao->show();
            } else {
                return "---";
            }
        }
    }

###########################################################

    function somatorioHoras4($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador
         * Recebe na forma de array para ser usada na classe de tabelas
         */
        # Separa as variaveis
        $idMarcador = 4;
        $horasExigidas = 20;
        $inscrito = $this->petec1($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }
        }
    }

    ###########################################################

    function somatorioHoras5($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 473/25
         */
        # Separa as variaveis
        $idMarcador = 5;
        $horasExigidas = 10;
        $inscrito = $this->petec1($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }
        }
    }

    ###########################################################

    function somatorioHoras6($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 481/25
         */
        # Separa as variaveis
        $idMarcador = 6;
        $horasExigidas = 20;
        $inscrito = $this->petec2($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }
        }
    }

    ###########################################################

    function somatorioHorasCompleto4($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador
         * Recebe na forma de array para ser usada na classe de tabelas
         */
        # Separa as variaveis
        $idMarcador = 4;
        $horasExigidas = 20;
        $inscrito = $this->petec1($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }

            # Exibe os dados da Portaria
            $this->exibeDadosPortaria($idMarcador);
        }
    }

    ###########################################################

    function somatorioHorasCompleto5($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 473/25
         */
        # Separa as variaveis
        $idMarcador = 5;
        $horasExigidas = 10;
        $inscrito = $this->petec1($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }

            # Exibe os dados da Portaria
            $this->exibeDadosPortaria($idMarcador);
        }
    }

    ###########################################################

    function somatorioHorasCompleto6($idServidor) {
        /**
         * Informa o somatorio de horas de um marcador 
         * Petec - Portaria 481/25
         */
        # Separa as variaveis
        $idMarcador = 6;
        $horasExigidas = 20;
        $inscrito = $this->petec2($idServidor);

        # Verifica se tem id
        if (empty($idServidor) OR empty($idMarcador)) {
            return 0;
        } else {

            # Pega as horas
            $formacao = new Formacao();
            $dados = $formacao->somatorioHoras($idServidor, $idMarcador);

            # Pega os valores
            $horasInformadas = $dados[0];
            $minutosInformados = $dados[1];

            # Formata para exibição
            if (empty($minutosInformados)) {
                $horasExibicao = "{$horasInformadas} h";
            } else {
                $horasExibicao = "{$horasInformadas} h e {$minutosInformados} m";
            }

            # Informa as horas
            p("Horas Informadas: {$horasExibicao}", "pHorasInformadas");
            p("Horas Exigidas: {$horasExigidas} h", "pHorasExigidas");

            # Calcula o que falta (se falta)
            $resultado = ($horasInformadas - $horasExigidas);

            # Verifica se está inscrito para esse Petec
            if (!$inscrito) {
                p("Servidor Não Inscrito", "pHorasFaltam");
            } else {
                # Se for inscrito exibe a situação
                if ($resultado >= 0) {
                    p("Situação OK", "pHoraOk");
                } else {
                    $resultado = abs($resultado);
                    if (empty($minutosInformados)) {
                        p("Faltam: {$resultado}h", "pHorasFaltam");
                    } else {
                        $resultado--;
                        $minutos = 60 - $minutosInformados;
                        p("Faltam: {$resultado}h e {$minutos} m", "pHorasFaltam");
                    }
                }
            }

            # Exibe os dados da Portaria
            $this->exibeDadosPortaria($idMarcador);
        }
    }

    ###########################################################

    function petec1($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec1
         */
        $select = "SELECT petec1
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if ($row[0] == "s") {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    function exibeIncricaoPetec1($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec1
         */
        if ($this->petec1($idServidor)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function petec2($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec2
         */
        $select = "SELECT petec2
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if ($row[0] == "s") {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    function exibeIncricaoPetec2($idServidor) {
        /**
         * Verifica se o servidor está inscrito no petec2
         */
        if ($this->petec2($idServidor)) {
            p("Inscrito", "pHoraOk");
        } else {
            p("Não Inscrito", "pHorasFaltam");
        }
    }

    ###########################################################

    function exibeDadosPetec($idServidor) {

        # Limita a Tela 
        $grid = new Grid();
        $grid->abreColuna(4);

        $this->exibeQuadroPortariasPetec();

        $grid->fechaColuna();
        $grid->abreColuna(8);

        /*
         * Exibe os dados dos certificados entregues
         */

        tituloTable("Dados dos Certificados PETEC Entregues");

        # Exibe a tabela do servidor
        $select = "SELECT tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor 
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $result2 = $pessoal->select($select);

        # Define as colunas
        $label = array();
        $align = array();
        $classe = array();
        $metodo = array();

        $formacao = new Formacao();
        $petec = $formacao->get_arrayMarcadores("Petec");

        foreach ($petec as $item) {
            $label[] = $item[1];
            $align[] = "center";
            $classe[] = "Petec";
            $metodo[] = "somatorioHorasCompleto{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
        }

        $tabela = new Tabela();
        #$tabela->set_titulo("Análise Geral");
        $tabela->set_conteudo($result2);

        $tabela->set_label($label);
        $tabela->set_align($align);

        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    function apagaTabelaCsv() {

        # Apaga a tabela 
        $select = 'SELECT idPetecImporta FROM tbpetecimporta';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $pessoal->set_tabela("tbpetecimporta");
        $pessoal->set_idCampo("idPetecImporta");

        foreach ($row as $tt) {
            $pessoal->excluir($tt[0]);
        }
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUpload() {


        $select = "SELECT idPetecImporta FROM tbpetecimporta";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUploadComErro() {


        $select = "SELECT idPetecImporta "
                . "  FROM tbpetecimporta "
                . " WHERE erro IS NOT NULL";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function exibeDadosPortaria($idMarcador) {

        # Exibe os dados da Portaria
        $dados = $this->get_arrayPetec($idMarcador);

        p("Prazo de Entrega: {$dados[3]}", "pPetecLabel");
        #p($dados[3], "pPetecInfo");
        p("Minimo de Horas: {$dados[2]}", "pPetecLabel");
        #p($dados[2], "pPetecInfo");
        p("Cursos a Partir de: {$dados[1]}", "pPetecLabel");
        #p($dados[1], "pPetecInfo");
    }

    ###########################################################
}
