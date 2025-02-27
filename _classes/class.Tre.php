<?php

class Tre {

    /**
     * Abriga as várias rotina do Cadastro de folgas fruidas e dias trabalhados do TRE
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $idServidor;
    private $diasTrabalhados;
    private $folgasConcedidas;
    private $folgasFruidas;
    private $folgasPendentes;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idservidor) {

        $pessoal = new Pessoal();
        $this->diasTrabalhados = $pessoal->get_treDiasTrabalhados($idservidor);
        $this->folgasConcedidas = $pessoal->get_treFolgasConcedidas($idservidor);
        $this->folgasFruidas = $pessoal->get_treFolgasFruidas($idservidor);
        $this->folgasPendentes = $this->folgasConcedidas - $this->folgasFruidas;
        $this->idServidor = $idservidor;
    }

    ###########################################################

    /**
     * exibe uma tabela com o resumo dos dados do TRE
     */
    function exibeResumo() {

        # Resumo
        $folgas = Array(
            Array('Dias Trabalhados', $this->diasTrabalhados),
            Array('Folgas Concedidas', $this->folgasConcedidas),
            Array('Folgas Fruídas', $this->folgasFruidas),
            Array('Folgas Pendentes', $this->folgasPendentes));

        $tabela = new Tabela("tabelaTre");
        $tabela->set_titulo('Resumo');
        $tabela->set_conteudo($folgas);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_align(['left']);
        $tabela->set_width([70, 30]);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => 'Folgas Pendentes',
                'operador' => '=',
                'id' => 'trePendente')));

        $tabela->show();
    }

    ###########################################################

    /**
     * exibe uma tabela com os dias trabalhados no TRE
     */
    function exibeDias() {


        # Dias Trabalhados e Folgas Concedidas
        $select = "SELECT YEAR(data),
                              data,
                              ADDDATE(data,dias-1),
                              dias,
                              folgas,
                              documento
                         FROM tbtrabalhotre
                        WHERE idServidor = {$this->idServidor} 
                     ORDER BY data desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo("Dias Trabalhados e Folgas Concedidas");
        $tabela->set_conteudo($row);
        $tabela->set_label(["Ano", "Início", "Término", "Dias", "Folgas<br/>Concedidas", "Documento"]);
        $tabela->set_align(['center', 'center', 'center', 'center', 'center', 'left']);
        $tabela->set_funcao([null, "date_to_php", "date_to_php"]);
        $tabela->set_width([10, 15, 15, 10, 10, 40]);

        $tabela->set_colunaSomatorio([3, 4]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);
        $tabela->show();
    }

    ###########################################################

    /**
     * exibe uma tabela com os dias trabalhados no TRE
     */
    function exibeFolgasFruídas() {

        # Folgas Fruídas
        $select = "SELECT YEAR(data),
                          data,
                          ADDDATE(data,dias-1),                                 
                          dias,
                          documento
                     FROM tbfolga
                    WHERE idServidor = {$this->idServidor} 
                 ORDER BY data desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Folgas Fruídas');
        $tabela->set_conteudo($row);
        $tabela->set_label(["Ano", "Início", "Término", "Folgas Fruídas", "Documento"]);
        $tabela->set_funcao([null, "date_to_php", "date_to_php"]);
        $tabela->set_align(["center", "center", "center", "center", "left"]);
        $tabela->set_width([10, 15, 15, 10, 50]);
        $tabela->set_colunaSomatorio(3);
        $tabela->set_totalRegistro(false);
        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);
        $tabela->show();
    }

    ###########################################################
}
