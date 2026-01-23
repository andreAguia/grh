<?php

class VagaDocentes {

    private $centro;
    private $laboratorio;
    private $situacao;
    private $cargo;

    public function setCentro($centro) {
        $this->centro = $centro;
    }

    public function setLaboratorio($laboratorio) {
        $this->laboratorio = $laboratorio;
    }

    public function setSituacao($situacao) {
        $this->situacao = $situacao;
    }

    public function setCargo($cargo) {
        $this->cargo = $cargo;
    }

    public function show() {

        # Conecta ao Banco de Dados        
        $pessoal = new Pessoal();

        $select = "SELECT idVaga,
                          IF((SELECT tbservidor.situacao FROM tbvagahistorico s JOIN tbconcurso USING (idConcurso) JOIN tbservidor USING (idServidor) WHERE s.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1) = 1,'Ocupada','Disponível'),
                          tbcargo.nome,
                          centro,
                          (SELECT idLotacao FROM tbvagahistorico l JOIN tbconcurso USING(idConcurso) WHERE l.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1),
                          idVaga,
                          (SELECT o.obs FROM tbvagahistorico o JOIN tbconcurso USING (idConcurso) WHERE o.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1),
                          (SELECT COUNT(idVagaHistorico) FROM tbvagahistorico c JOIN tbconcurso USING (idConcurso) WHERE c.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc),
                          idVaga,
                          idVaga
                     FROM tbvaga p LEFT JOIN tbcargo USING (idCargo)
                    WHERE centro = '{$this->centro}' 
                      AND idCargo = {$this->cargo}";
        
        # Laboratório de Origem
        if ($this->laboratorio <> "*") {
            $select .= " AND (SELECT idLotacao FROM tbvagahistorico s JOIN tbconcurso USING(idConcurso) WHERE s.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1) = {$this->laboratorio}";
        }

        # Situação
        if ($this->situacao == "Disponível") {
            $select .= " AND (
                ((SELECT tbservidor.situacao FROM tbvagahistorico s JOIN tbconcurso USING (idConcurso) JOIN tbservidor USING (idServidor) WHERE s.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1) <> 1)
             OR ((SELECT tbservidor.situacao FROM tbvagahistorico s JOIN tbconcurso USING (idConcurso) JOIN tbservidor USING (idServidor) WHERE s.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1) IS NULL))";
        } elseif($this->situacao == "Ocupada") {
            $select .= " AND 
                 (SELECT tbservidor.situacao FROM tbvagahistorico s JOIN tbconcurso USING (idConcurso) JOIN tbservidor USING (idServidor) WHERE s.idVaga = p.idVaga ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1) = 1";
        }

        $select .= " ORDER BY idVaga";

        #echo $select;

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        if($this->situacao == "Disponível"){
            $tabela->set_titulo("Vagas Disponíveis");
        }else{
            $tabela->set_titulo("Vagas Ocupadas");
        }
        
        $tabela->set_conteudo($result);
        $tabela->set_label(["Vaga", "Situação", "Cargo", "Centro", "Laboratório de Origem", "Último Ocupante", "Obs", "Num. de Concursos", "Problemas", "Editar"]);
        $tabela->set_align(["center", "center", "center", "center", "center", "left", "left"]);
        $tabela->set_classe([null, null, null, null, "Pessoal", "Vaga",null,null,"Vaga"]);
        $tabela->set_metodo([null, null, null, null, "get_lotacaoGerencia", "get_servidorOcupante",null,null,"temProblema"]);
        $tabela->set_funcao(["ressaltaVaga"]);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 1,
                'valor' => 'Disponível',
                'operador' => '=',
                'id' => 'emAberto'),
            array('coluna' => 1,
                'valor' => 'Ocupada',
                'operador' => '=',
                'id' => 'alerta')
        ));

        $tabela->set_excluirCondicional('cadastroVagas.php?fase=excluir', 0, 7, "==");

        # Botão de Editar concursos
        $botao1 = new BotaoGrafico();
        $botao1->set_label('');
        $botao1->set_title('Editar o Concurso');
        $botao1->set_url("?fase=editarConcurso&id=");
        $botao1->set_imagem(PASTA_FIGURAS . 'olho.png', 20, 20);

        # Coloca o objeto link na tabela			
        $tabela->set_link([null, null, null, null, null, null, null, null, null, $botao1]);
        $tabela->set_idCampo('idVaga');
        $tabela->show();
    }

}
