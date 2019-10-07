<?php

class ExibeFoto{
    /**
     * Gera o Menu Principal do Sistema
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    
    private $fotoLargura = 70;
    private $fotoAltura = 90;
    private $url = '#';
    
######################################################################################################################    
    
    public function __construct(){
    /**
     * Inicia a classe
     */
        
    }

######################################################################################################################

    public function set_fotoLargura($fotoLargura = NULL){
    /**
     * Informa a largura da foto
     * 
     * @syntax $foto->set_fotoLargura($fotoLargura);
     * 
     * @param $fotoLargura int NULL A largura da foto
     */
    
        $this->fotoLargura = $fotoLargura;
    }

######################################################################################################################

    public function set_fotoAltura($fotoAltura = NULL){
    /**
     * Informa a altura da foto
     * 
     * @syntax $foto->set_fotoAltura($fotoAltura);
     * 
     * @param $fotoAltura int NULL A altura da foto
     */
    
        $this->fotoAltura = $fotoAltura;
    }

######################################################################################################################

    public function set_url($url = NULL){
    /**
     * Informa a url da foto
     * 
     * @syntax $foto->set_url($url);
     * 
     * @param $url int NULL A url da foto
     */
    
        $this->url = $url;
    }

######################################################################################################################
    
    /**
     * Método show
     * 
     * Exibe a Foto do servidor
     */
    
    public function show($idPessoa){
        
        # Monta o Menu
        $menu = new MenuGrafico(1);
            
        # Define a pasta
        $arquivo = "../../_fotos/$idPessoa.jpg";

        # Verifica se tem pasta desse servidor
        if(file_exists($arquivo)){
            $botao = new BotaoGrafico("foto");
            $botao->set_url($this->url);
            $botao->set_imagem($arquivo,$this->fotoLargura,$this->fotoAltura);
            $botao->set_title('Foto do Servidor');
            $menu->add_item($botao);
        }else{
            $botao = new BotaoGrafico("foto");
            $botao->set_url($this->url);
            $botao->set_imagem(PASTA_FIGURAS.'foto.png',$this->fotoLargura,$this->fotoAltura);
            $botao->set_title('Servidor sem foto cadastrada');
            $menu->add_item($botao);
        }

        $menu->show();
    }
   
######################################################################################################################
   
}