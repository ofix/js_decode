var ClassNameEmitter = function(code){
	var len = code.length;
	var tokens = [];
	var stacks = [];
	var template_begin = false;
	var include_begin = false;
	var define_begin = false;
	var compiler_conn_begin = false;
	var word;
	var words = [];

	var keywords = ['template','class'];
	for(var i=0; i<len; i++){
		var c = code[i];//c means char
		switch(c){
			case 'A':case 'B':case 'C':case 'D':
			case 'E':case 'F':case 'G':case 'H':
			case 'I':case 'J':case 'K':case 'L':
			case 'M':case 'N':case 'O':case 'P':
			case 'Q':case 'R':case 'S':case 'T':
			case 'U':case 'V':case 'W':case 'X':
			case 'Y':case 'Z':case 'a':case 'b':
			case 'c':case 'd':case 'e':case 'f':
			case 'g':case 'h':case 'i':case 'j':
			case 'k':case 'l':case 'm':case 'n':
		    case 'o':case 'p':case 'q':case 'r':
			case 's':case 't':case 'u':case 'v':
			case 'w':case 'x':case 'y':case 'z':
			case '_':case '0':case '1':case '2':
			case '3':case '4':case '5':case '6':
			case '7':case '8':case '9':{
				word+=c;
				break;
			}
			case ' ':{//空格
				while((c=code[i++])==' ');
				i--;
				if(word !=''){
					words += word;
					word = '';
				}
				break;
			}
			case '<':{//模板标记
				if(word == 'template'){ //模板开始匹配
					template_begin = true;
				}
			}
			case '#':{//maybe #include,#define,a##b,/*##*/,"###"
				if(code[i+1]=='i'&&code[i+2]=='n'&&code[i+3]=='c'
					&&code[i+4]=='l'&&code[i+5]=='d'&&code[i+6]=='e'&&code[i+7]==' '){
					include_begin = true;
				}else if(code[i+1]=='d'&&code[i+2]=='e'&&code[i+3]=='f'&&code[i+4]=='i'
					&&code[i+5]=='n'&&code[i+6]=='e'&&code[i+7]==' '){
					define_begin = true;
				}else if(code[i+1]=='#'){
					compiler_conn_begin = true;
				}
				break;
			}
			case '"':{
				var j = ParseString(code,i,len);
				word = code.substr(i,j);
				i = j;
				break;
			}
			case '\'':{
				c = code[i];
				word += c;
				break; 
			}
			case '(':{

			}

		}
		
	}
};

ParseParameters(code,i,len){
	switch(code[i++]){
		case ',':
		case '<':{
			
			break;
		}
		case '"':
		case '(':
		case ')':
		default:
			break;
	}
}

ParseString(code,i,len){
	var j=i;
	for(;j<len;j++){
		if(code[j]=='\\'){
			j++;
			continue;
		}
		if(code[j]=='"'){
			break;
		}
	}
	return j;
}