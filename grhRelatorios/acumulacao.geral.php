<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

# Pega os parâmetros
$parametroNomeMat = get_session('parametroNomeMat');

if($acesso){
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######   
    
    # Título & Subtitulo
    $subTitulo = NULL;
    $titulo = "Servidores com Acumulação de Cargo Público";

    # Pega os dados
            $select = "SELECT CASE conclusao
                                WHEN 1 THEN 'Pendente'
                                WHEN 2 THEN 'Resolvido'
                                ELSE '--'
                              END,
                              idAcumulacao,
                              idFuncional,
                              tbpessoa.nome,
                              dtProcesso,
                              processo,
                              instituicao,
                              cargo,
                              tbacumulacao.matricula,
                              tbservidor.idServidor
                         FROM tbacumulacao JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.idPerfil <> 10";
            
    # nome
    if(!is_null($parametroNomeMat)){
        $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
    }

    $select .= " ORDER BY conclusao, tbpessoa.nome";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    $relatorio->set_label(array("Conclusão","Resultado","idFuncional","Nome","Data","Processo","Instituição","Cargo","Matrícula"));
    $relatorio->set_align(array("center","center","center","left","center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_width(array(5,5,5,20,5,20,15,15,5));

    $relatorio->set_classe(array(NULL,"Acumulacao"));
    $relatorio->set_metodo(array(NULL,"get_resultado"));

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->show();

    $page->terminaPagina();
}