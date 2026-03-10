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
        } else {
            $this->nomeCampo = "petec2";
        }

        # Forma do resultado
        $this->relatorio = $relatorio;

        # Trata o nome da Lotação
        if ($this->lotacao <> "Todos") {
            if (is_numeric($this->lotacao)) {
                $pessoal = new Pessoal();
                $this->nomeLocacao = $pessoal->get_nomeLotacao($this->lotacao);
            } else {
                $this->nomeLocacao = $this->lotacao;
            }
        }
    }

    ##############################################################

    public function get_arrayNaoEntregaram() {

        $novoArray = array();

        # Inicia a Classe
        $pessoal = new Pessoal();

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

        # Percorre o array
        $nomeMetodo = "get_somatorioArredondadoHoras{$this->idMarcador}";

        $petec = new Petec();
        foreach ($result2 as $item) {
            $somatorioHoras = $petec->$nomeMetodo($item["idServidor"]);
            if ($somatorioHoras == 0) {
                $novoArray[] = [
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"]];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function get_arrayHorasInsuficientes() {

        $novoArray = array();

        # Inicia a Classe
        $pessoal = new Pessoal();

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

        # Percorre o array
        $nomeMetodo = "get_somatorioArredondadoHoras{$this->idMarcador}";

        $petec = new Petec();
        foreach ($result2 as $item) {
            $somatorioHoras = $petec->$nomeMetodo($item["idServidor"]);
            if ($somatorioHoras > 0 AND $somatorioHoras < $this->horas) {
                $novoArray[] = [
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"]];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function get_arraySituacaoRegula() {

        $novoArray = array();

        # Inicia a Classe
        $pessoal = new Pessoal();

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

        # Percorre o array
        $nomeMetodo = "get_somatorioArredondadoHoras{$this->idMarcador}";

        $petec = new Petec();
        foreach ($result2 as $item) {
            $somatorioHoras = $petec->$nomeMetodo($item["idServidor"]);
            if ($somatorioHoras >= $this->horas) {
                $novoArray[] = [
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"],
                    $item["idServidor"]];
            }
        }

        return $novoArray;
    }

    ##############################################################

    public function exibeTituloGeral() {

        # Exibe o título
        tituloTable("Portaria Petec {$this->portaria}<br/>{$this->nomeLocacao}", null, "{$this->horas} horas");
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
        }else{
            $subtitulo = "Servidores Inscritoe e Não Inscritos";
        }

        if ($this->relatorio) {
            $tabela = new Relatorio();
            $tabela->set_titulo("Portaria Petec {$this->portaria}");
            $tabela->set_tituloLinha2($this->nomeLocacao);
            $tabela->set_tituloLinha3('Servidores em Situação Irregular');
            $tabela->set_subtitulo("Servidores Que NÃO Entregaram Certificados<br/>({$this->horas} Horas)");
            #$tabela->set_totalRegistro(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_dataImpressao(false);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Total<br/>de Horas"]);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Que NÃO Entregaram Certificados');
            $tabela->set_subtitulo($subtitulo);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Total<br/>de Horas", "Editar"]);
        }

        $tabela->set_width([15, 15, 50, 10, 10, 10]);
        $tabela->set_conteudo($this->get_arrayNaoEntregaram());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "Petec"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoELotacao", "get_perfil", "get_somatorioArredondadoHoras{$this->idMarcador}"]);

        if (!$this->relatorio) {
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
        }

        # Botão Editar
        if (!$this->relatorio) {
            $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, $botao]);
        }
        $tabela->show();
    }

    ##############################################################

    public function horasInsuficientes() {
        
        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        }else{
            $subtitulo = "Servidores Inscritoe e Não Inscritos";
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
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Total<br/>de Horas"]);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Com Horas Insuficientes');
            $tabela->set_subtitulo($subtitulo);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Total<br/>de Horas", "Editar"]);
        }

        $tabela->set_width([15, 15, 50, 10, 10, 10]);
        $tabela->set_conteudo($this->get_arrayHorasInsuficientes());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "Petec"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoELotacao", "get_perfil", "get_somatorioArredondadoHoras{$this->idMarcador}"]);

        if (!$this->relatorio) {
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
        }

        # Botão Editar
        if (!$this->relatorio) {
            $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, $botao]);
        }
        $tabela->show();
    }

    ##############################################################

    public function situacaoRegular() {
        
        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        }else{
            $subtitulo = "Servidores Inscritoe e Não Inscritos";
        }

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Em Situação Regular');
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["IdFuncional<br/>Matrícula", "Inscrito?", "Servidor", "Perfil", "Total<br/>de Horas", "Editar"]);
        $tabela->set_width([15, 15, 50, 10, 10, 10]);
        $tabela->set_conteudo($this->get_arraySituacaoRegula());
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_classe(['pessoal', "Petec", "pessoal", "pessoal", "Petec"]);
        $tabela->set_metodo(["get_idFuncionalEMatricula", "exibeIncricao" . plm($this->nomeCampo), "get_nomeECargoELotacao", "get_perfil", "get_somatorioArredondadoHoras{$this->idMarcador}"]);

        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);

        # Botão Editar
        $botao = new Link(null, "{$this->linkServidor}&id=", 'Acessa o servidor');
        $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

        # Coloca o objeto link na tabela			
        $tabela->set_link([null, null, null, null, null, $botao]);
        $tabela->show();
    }

    ##############################################################

    public function exibeQuadroQuantidades() {

        $arrayTabela = [
            ["Servidores Que NÃO Entregaram Certificados", count($this->get_arrayNaoEntregaram())],
            ["Servidores Com Horas Insuficientes", count($this->get_arrayHorasInsuficientes())],
            ["Servidores Em Situação Regular", count($this->get_arraySituacaoRegula())]
        ];

        $tabela = new Tabela();
        $tabela->set_titulo("Resumo");
        
        # Inscrição
        if ($this->inscricao <> "Todos") {
            if ($this->inscricao == "Inscritos") {
                $subtitulo = "Servidores Inscritos";
            } else {
                $subtitulo = "Servidores NÃO Inscritos";
            }
        }else{
            $subtitulo = "Servidores Inscritoe e Não Inscritos";
        }
        
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Situação", "Quantidade"]);
        $tabela->set_width([70, 30]);
        $tabela->set_conteudo($arrayTabela);
        $tabela->set_align(["left"]);
        $tabela->set_colunaSomatorio(1);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################
}
