<?php

class ListaPetec {

    /**
     * Exibe uma lista do Petec
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $idMarcador = null;
    private $portaria = null;
    private $publicao = null;
    private $horas = null;
    private $entrega = null;
    private $pdf = null;
    private $lotacao = null;
    private $inscricao = null;
    private $linkServidor = null;
    private $nomeCampo = null;
    private $relatorio = null;
    private $nomeLotacao = null;

    ##############################################################

    public function __construct($idMarcador = null, $lotacao = null, $inscricao = null, $linkServidor = null, $relatorio = false) {
        /**
         * Inicia a Classe somente
         * 
         * @param $idConcurso integer null O id do concurso
         * 
         * @syntax $concurso = new Concurso([$idConcurso]);
         */
        $this->idMarcador = $idMarcador;

        # Pega os dados na Classe Petec
        $petec = new Petec();
        $dados = $petec->get_arrayPetec($idMarcador);

        # Passa para as variáveis
        $this->portaria = $dados[0];
        $this->publicao = $dados[1];
        $this->horas = $dados[2];
        $this->entrega = $dados[3];
        $this->pdf = $dados[4];

        # Variáveis da pesquisa
        $this->lotacao = $lotacao;
        $this->inscricao = $inscricao;
        $this->linkServidor = $linkServidor;

        # Do nome do campo na tabela
        if ($this->idMarcador == 4 OR $this->idMarcador == 5) {
            $this->nomeCampo = "petec1";
        }

        if ($this->idMarcador == 6) {
            $this->nomeCampo = "petec2";
        }

        if ($this->idMarcador == 8) {
            $this->nomeCampo = "petec3";
        }

        # Forma do resultado
        $this->relatorio = $relatorio;

        # Trata o nome da Lotação
        if ($this->lotacao <> "Todos") {
            if (is_numeric($this->lotacao)) {
                $pessoal = new Pessoal();
                $this->nomeLotacao = $pessoal->get_nomeLotacao($this->lotacao);
            } else {
                $this->nomeLotacao = $this->lotacao;
            }
        }
    }

    ##############################################################

    public function get_arrayNaoEntregaram() {

        $novoArray = array();

        # Inicia as Classes
        $pessoal = new Pessoal();
        $formacao = new Formacao();

        $select2 = "SELECT tbservidor.idServidor,
                           tbpessoa.nome
                      FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      LEFT JOIN tbperfil USING (idPerfil)
                                           JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                      WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

        # Verifica se tem filtro por lotação
        if ($this->lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select2 .= " AND tblotacao.idlotacao = {$this->lotacao}";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND tblotacao.DIR = '{$this->lotacao}'";
            }
        }

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $select2 .= " AND tbservidor.{$this->nomeCampo} = 's'";
            } else {
                $select2 .= " AND (tbservidor.{$this->nomeCampo} <> 's' OR tbservidor.{$this->nomeCampo} IS NULL)";
            }
        }

        $select2 .= " ORDER BY tbpessoa.nome";
        $result2 = $pessoal->select($select2);

        foreach ($result2 as $item) {

            # Pega o somatório das horas (somente horas e não minutos)
            $somatorioHoras = $formacao->somatorioHoras($item["idServidor"], $this->idMarcador);

            # Os não entregues o somatório é zero
            if ($somatorioHoras[0] == 0) {
                $novoArray[] = [
                    $item["idServidor"], // Matricula
                    $item["idServidor"], // Inscrito
                    $item["idServidor"], // Nome do Servidor e Cargo
                    $item["idServidor"], // Lotação
                    $item["idServidor"], // Perfil
                    $formacao->exibeSomatorioHorasMinutos($item["idServidor"], $this->idMarcador), // Horas
                    $item["idServidor"]  // Botão Editar
                ];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function get_arrayHorasInsuficientes() {

        $novoArray = array();

        # Inicia as Classes
        $pessoal = new Pessoal();
        $formacao = new Formacao();

        $select2 = "SELECT tbservidor.idServidor,
                               tbpessoa.nome
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

        # Verifica se tem filtro por lotação
        if ($this->lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select2 .= " AND tblotacao.idlotacao = {$this->lotacao}";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND tblotacao.DIR = '{$this->lotacao}'";
            }
        }

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $select2 .= " AND tbservidor.{$this->nomeCampo} = 's'";
            } else {
                $select2 .= " AND (tbservidor.{$this->nomeCampo} <> 's' OR tbservidor.{$this->nomeCampo} IS NULL)";
            }
        }

        $select2 .= " ORDER BY tbpessoa.nome";
        $result2 = $pessoal->select($select2);

        foreach ($result2 as $item) {

            # Pega o somatório das horas (somente horas e não minutos)
            $somatorioHoras = $formacao->somatorioHoras($item["idServidor"], $this->idMarcador);

            # Horas Insuficientes são de 0 às horas exigidas na Portaria
            if ($somatorioHoras[0] > 0 AND $somatorioHoras[0] < $this->horas) {
                $novoArray[] = [
                    $item["idServidor"], // Matricula
                    $item["idServidor"], // Inscrito
                    $item["idServidor"], // Nome do Servidor e Cargo
                    $item["idServidor"], // Lotação
                    $item["idServidor"], // Perfil
                    $formacao->exibeSomatorioHorasMinutos($item["idServidor"], $this->idMarcador), // Horas
                    $item["idServidor"]  // Botão Editar
                ];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function get_arraySituacaoRegular() {

        $novoArray = array();

        # Inicia as Classes
        $pessoal = new Pessoal();
        $formacao = new Formacao();

        $select2 = "SELECT tbservidor.idServidor,
                               tbpessoa.nome
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbperfil.tipo <> 'Outros'
                        AND situacao = 1";

        # Verifica se tem filtro por lotação
        if ($this->lotacao <> "Todos") {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select2 .= " AND tblotacao.idlotacao = {$this->lotacao}";
            } else { # senão é uma diretoria genérica
                $select2 .= " AND tblotacao.DIR = '{$this->lotacao}'";
            }
        }

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $select2 .= " AND tbservidor.{$this->nomeCampo} = 's'";
            } else {
                $select2 .= " AND (tbservidor.{$this->nomeCampo} <> 's' OR tbservidor.{$this->nomeCampo} IS NULL)";
            }
        }

        $select2 .= " ORDER BY tbpessoa.nome";
        $result2 = $pessoal->select($select2);

        foreach ($result2 as $item) {

            # Pega o somatório das horas (somente horas e não minutos)
            $somatorioHoras = $formacao->somatorioHoras($item["idServidor"], $this->idMarcador);

            # Situação regular é igual ou maior que as horas da portaria
            if ($somatorioHoras[0] >= $this->horas) {
                $novoArray[] = [
                    $item["idServidor"], // Matricula
                    $item["idServidor"], // Inscrito
                    $item["idServidor"], // Nome do Servidor e Cargo
                    $item["idServidor"], // Lotação
                    $item["idServidor"], // Perfil
                    $formacao->exibeSomatorioHorasMinutos($item["idServidor"], $this->idMarcador), // Horas
                    $item["idServidor"]  // Botão Editar
                ];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function exibeTituloGeral() {

        # Exibe o título
        #tituloTable("Portaria Petec {$this->portaria}", null, $this->nomeLotacao);

        $petec = new Petec();
        $dados = $petec->get_arrayPetec($this->idMarcador);

        $arraydeFato = [[
        $dados[2], // Horas Exigidas
        $dados[3], // Data Limite da Entrega
        $dados[1], // Data do Certificado
        $dados[5], // Meses
        $dados[6], // Valor            
        $dados[4], // Pdf            
        ]];

        $tabela = new Tabela();
        $tabela->set_titulo("Portaria Petec {$this->portaria}");
        $tabela->set_subtitulo($this->nomeLotacao);
        $tabela->set_conteudo($arraydeFato);
        $tabela->set_label(["Horas Exigidas", "Entregar até", "Curso iniciado a partir de", "Meses de PGTO", "Valor", "Pdf"]);
        $tabela->set_totalRegistro(false);

        $tabela->set_classe([null, null, null, null, null, "Petec"]);
        $tabela->set_metodo([null, null, null, null, null, "exibePdfPetec"]);

        $formatacaoCondicional = array(
            array('coluna' => 0,
                'valor' => $dados[2],
                'operador' => '=',
                'id' => 'listaDados'));

        $tabela->set_formatacaoCondicional($formatacaoCondicional);
        $tabela->show();
    }

    ##############################################################

    public function exibeNaoEntregaram() {

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        } else {
            $subtitulo = "Servidores Inscritos e Não Inscritos";
        }

        if ($this->relatorio) {
            $tabela = new Relatorio();
            $tabela->set_titulo("Portaria Petec {$this->portaria}");
            $tabela->set_tituloLinha2($this->nomeLotacao);
            $tabela->set_tituloLinha3('Servidores em Situação Irregular');
            $tabela->set_subtitulo("Servidores Que NÃO Entregaram Certificados");

            $tabela->set_bordaInterna(true);
            $tabela->set_dataImpressao(false);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Lotação", "Perfil", "Horas"]);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Que NÃO Entregaram Certificados');
            $tabela->set_subtitulo($subtitulo);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Lotação", "Perfil", "Horas", "Editar"]);
        }

        $tabela->set_width([10, 10, 30, 25, 10, 10, 5]);
        $tabela->set_conteudo($this->get_arrayNaoEntregaram());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "pessoal"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoSimples", "get_lotacao", "get_perfil"]);

        if (!$this->relatorio) {
//            $tabela->set_rowspan(0);
//            $tabela->set_grupoCorColuna(0);
        }

        # Botão Editar
        if (!$this->relatorio) {
            $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, $botao]);
        }
        $tabela->show();
    }

    ##############################################################

    public function exibeHorasInsuficientes() {

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        } else {
            $subtitulo = "Servidores Inscritos e Não Inscritos";
        }


        if ($this->relatorio) {
            br(2);
            $tabela = new Relatorio();
            #$tabela->set_titulo("Portaria Petec {$this->portaria}");
            #$tabela->set_tituloLinha2('Servidores em Situação Irregular');
            $tabela->set_subtitulo('Servidores Com Horas Insuficientes');

            $tabela->set_subTotal(false);
            #$tabela->set_totalRegistro(false);
            #$tabela->set_dataImpressao(false);
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_log(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Lotação", "Perfil", "Horas"]);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Com Horas Insuficientes');
            $tabela->set_subtitulo($subtitulo);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Lotação", "Perfil", "Horas", "Editar"]);
        }

        $tabela->set_width([10, 10, 30, 25, 10, 10, 5]);
        $tabela->set_conteudo($this->get_arrayHorasInsuficientes());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "pessoal"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoSimples", "get_lotacao", "get_perfil"]);

        if (!$this->relatorio) {
//            $tabela->set_rowspan(0);
//            $tabela->set_grupoCorColuna(0);
        }

        # Botão Editar
        if (!$this->relatorio) {
            $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, null, $botao]);
        }
        $tabela->show();
    }

    ##############################################################

    public function exibeSituacaoRegular() {

        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        } else {
            $subtitulo = "Servidores Inscritos e Não Inscritos";
        }

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Em Situação Regular');
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Lotação", "Perfil", "Horas", "Editar"]);
        $tabela->set_width([10, 10, 30, 25, 10, 10, 5]);
        $tabela->set_conteudo($this->get_arraySituacaoRegular());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "pessoal"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoSimples", "get_lotacao", "get_perfil"]);

//        $tabela->set_rowspan(0);
//        $tabela->set_grupoCorColuna(0);
        # Botão Editar
        $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
        $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

        # Coloca o objeto link na tabela			
        $tabela->set_link([null, null, null, null, null, null, $botao]);
        $tabela->show();
    }

    ##############################################################

    public function exibeQuadroQuantidades() {

        $arrayTabela = [
            ["Não Entregaram Certificados", count($this->get_arrayNaoEntregaram())],
            ["Com Horas Insuficientes", count($this->get_arrayHorasInsuficientes())],
            ["Em Situação Regular", count($this->get_arraySituacaoRegular())]
        ];

        $tabela = new Tabela();
        $tabela->set_titulo("Resumo");
        #$tabela->set_titulo("Resumo<br/>{$this->nomeLotacao}");
        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        } else {
            $subtitulo = "Servidores Inscritos e Não Inscritos";
        }

        #$tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Servidores", "Quantidade"]);
        $tabela->set_width([70, 30]);
        $tabela->set_conteudo($arrayTabela);
        $tabela->set_align(["left"]);
        $tabela->set_colunaSomatorio(1);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################
}
