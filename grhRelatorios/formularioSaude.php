<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Bem Estar e Saúde");
    $page->iniciaPagina();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    ######
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio2($idServidorPesquisado, 'Dados de Saúde e Bem Estar');
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
    p("Oxigenação Sanguineía: _____________________", 'pFormSaude');

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
    
    tituloRelatorio2('Quais desse profissionais você costuma acessar para cuidar de sua saúde?');

    $grid->fechaColuna();
    $grid->abreColuna(6);

    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Nutricionista", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Oftalmologista", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Cardiologista", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Aícademia para Atividade Física", 'pFormSaude');

    $grid->fechaColuna();
    $grid->abreColuna(6);

    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Clínico Geralí", 'pFormSaude');
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
     * Genética
     */
    
    tituloRelatorio2('Na sua genética ou família, quais doenças crônicas existem?');

    $grid->fechaColuna();
    $grid->abreColuna(6);

    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Diabetes", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Hipertensão", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Cardiopatia", 'pFormSaude');
    
    $grid->fechaColuna();
    $grid->abreColuna(6);
    
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Câncer", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Nâo há doenças", 'pFormSaude');
    p("[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]&nbsp; Outro: ___________________________", 'pFormSaude');

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

    $page->terminaPagina();
}