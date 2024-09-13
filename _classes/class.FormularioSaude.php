<?php

class FormularioSaude {

    /**
     * Emite uma formulario de saude
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    public function __construct($idServidor = null, $idUsuario = null, $menuRelatorio = true) {

        # Trata as variáveis
        if (empty($idServidor)) {
            return null;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Grava no log a atividade
        $atividade = "Visualizou o formulário de saúde e bem estar";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 4, $idServidor);

        # Começa uma nova página
        $page = new Page();
        $page->set_title("Bem Estar e Saúde");
        $page->iniciaPagina();

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        ######
        # Dados do Servidor
        Grh::listaDadosServidorRelatorio3($idServidor, 'Dados de Saúde e Bem Estar', null, $menuRelatorio, null, "geral");
        br();

        /*
         * Dados Gerais
         */

        #tituloRelatorio2('Dados Gerais');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("Idade: _____________________", 'pFormSaude');
        p("Peso: _____________________", 'pFormSaude');
        p("Altura: _____________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("Pressão Arterial: _____________________", 'pFormSaude');
        p("Glicose: __________________________________", 'pFormSaude');
        p("Oxigenação Sanguínea: _____________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Exercicio
         */

        tituloRelatorio2('Com que frequência se exercita?');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 1 vez por semana", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 3 vezes por semana", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Acima de 3 vezes por semana", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Sedentário", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Exames
         */

        tituloRelatorio2('Com que frequência faz exames de rotina?');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 1 vez por trimestre", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 1 vez por semestre", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 1 vez por ano", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; 1 vez em períodos maiores que 1 ano", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Somente em caso de emergência", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Outro: ___________________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Profissionais
         */

        tituloRelatorio2('Quais desses profissionais você costuma acessar para cuidar de sua saúde?');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Nutricionista", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Oftalmologista", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Cardiologista", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Academia para Atividade Física", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Clínico Geral", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Endocrinologista", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Educador Físico", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Outro: ___________________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Rotina Alimentar
         */

        tituloRelatorio2('Pense sobre sua rotina alimentar, na maioria da semana ela é composta de:');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Fast Food", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Refeições caseiras regulares", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Refeições orientadas por nutricionistas regulares", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Refeições caseiras ou fora de casa irregulares", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Outro: ___________________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Doenças
         */

        tituloRelatorio2('Você tem ou teve algumas destas doenças?');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Diabetes", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Hipertensão", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Cardiopatia", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Câncer", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Depressão", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Ansiedadeã", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Não há doenças", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Outro: ___________________________", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Vacinas
         */

        tituloRelatorio2('Indique as vacinas que você está com as doses atualizadas?');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Gripe (anual)", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Hepatite", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(6);

        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Tétano e Difteria", 'pFormSaude');
        p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Covid-19", 'pFormSaude');

        $grid->fechaColuna();
        $grid->abreColuna(12);

        /*
         * Genética
         */

        tituloRelatorio2('Fique à vontade para falar de outros hábitos de prevenção que você faça:');

        p("_______________________________________________________________________________________________", 'pFormSaudeLinha');
        p("_______________________________________________________________________________________________", 'pFormSaudeLinha');
        #p("___________________________________________________________________________________________________", 'pFormSaudeLinha');

        p("Parabéns, adorei conhecer você e te ajudar a refletir sobre a sua vida.", 'pFormSaudeMensagem');

        $grid->fechaColuna();
        $grid->fechaGrid();

        echo "<p style='page-break-before:always'></p>";

        $page->terminaPagina();
    }
}
