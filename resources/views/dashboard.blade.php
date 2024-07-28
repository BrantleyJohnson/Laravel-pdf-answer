<x-app-layout>
        <div class="chat-container flex-1">
            <aside class="sidebar" style="min-width: 20%">
              <!-- <p class="underline text-center text-sm">References</p> -->
              <div x-html="referenceDivs"> </div>
              <div class="flex d-flex" style="background-color: #e5e5ea; font-weight: 600; font-size: 25;">
                    <button x-show="dislike_ar" class="flex-col-3 text-center " style="padding:50px; z-index: 1;" @click="reTrain()">Accept</button>
                    <button x-show="dislike_ar" class="flex-3 text-center" style="z-index: 1;" @click="reject()">Reject</button>
                  </div>
            </aside>
            <main class="chat-main" x-data="{'is_reply': true}" >
                <div class="chat-history" id="chat-div" @scroll.debounce.1000ms="handleScroll()">
                  <template x-for="message in loadMessages" :key="message.id">
                    <div class="message-master chat-message mb-4"
                    x-data="{'reference': message.reference, 'copied': false, 'is_speaking': false, 'is_dislike': 0 }"
                    x-bind:reference="message.reference"
                    :class="{'user-question': message.type === 'user', 'ai-response-reference': message.type === 'response'}"
                    > 
                      <div class="flex flex-row" 
                      x-init="addReferenceDiv($el, message.type === 'response', message.reference)">
                          <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden rounded-full " style="background-color: rgb(28 25 23);">
                            <span class="text-sm text-white" style="padding: 2px;" >
                              <p x-show="(message.type === 'user')">{{ $initials }}</p>
                              <p x-show="(message.type === 'response')">{{ env("APP_INITIALS") }}</p>
                            </span>   
                          </div>
                          <div>
                            <span class="mx-1">
                              <p x-show="(message.type === 'user')">&nbsp;&nbsp;{{ $name }}</p>
                              <p  x-show="(message.type === 'response')">&nbsp;&nbsp;{{ env("APP_NAME") }}</p>
                            </span>
                          </div>
                      </div>

                      <div
                        class=" text-base rounded-md mb-1 p-2 mt-1"
                        :class="{'outgoing': message.type === 'user', 'incoming': message.type === 'response'}"
                        style="margin-left: 2rem;"
                      >                   
                        <div x-html="message.text"></div>
                        <button x-show="(message.type === 'response')" @click="replyAnswer(message.text)"><i class="fa-2xs fa-solid fa-reply"></i></button>
                      </div>
                      <div style="margin-left: 2rem;">
                        <button x-show="(message.type === 'user')"><i class="fa-2xs fa-solid fa-pen"></i></button>
                        <button  x-show="(message.type === 'response')">
                          <i x-show="!is_speaking" @click="speak(message.text)" class="fa-2xs fa-solid fa-volume-high"></i>
                          <i x-show="is_speaking" class="fa-2xs fa-solid fa-volume-low"></i>
                        </button>
                        <button x-show="(message.type === 'response')" @click="copyToClipboard(message.text)">
                          <i x-show="!copied" class="fa-2xs fa-solid fa-clipboard"></i>
                          <i x-show="copied" class="fa-2xs fa-solid fa-check-double" style="color: #4ade80;"></i>
                        </button>
                        <button x-show="(message.type === 'response')" @click="reloadAnswer()"><i class="fa-2xs fa-solid fa-arrow-rotate-left"></i></button>
                        <button x-show="(message.type === 'response')" @click="dislike()"><i class="fa-2xs fa-solid fa-thumbs-down"></i></button>
                        <button @click="shareData(message.text)"><i class="fa-2xs fa-solid fa-share"></i></button>
                      </div>

                      <div x-show="is_dislike == 1">
                        <form @submit.prevent="sendDislike()">
                          <label>comment</label> <br />
                          <input type="text" x-model="newComment" placeholder="Type your comment here..." />
                          <button type="submit">Send</button>
                        </form>
                      </div>

                    </div>
                  </template>
                  
                </div>
                <div x-show="!is_reply" x-html="reply"  style="background-color: lightgrey; padding: 20px; margin-left: 3%; margin-right:15%"></div> 
                
                @if(!isset($sharable_content) || !$sharable_content )
                <form x-show="adminview" class="chat-form" @submit.prevent="sendMessage()">
                  <input
                      type="text"
                      x-model="newMessage"
                      placeholder="Type your message here..."
                    />
                    
                  <template x-if="allowMessage">
                    <button type="submit">Send</button>
                  </template>
                  <template x-if="!allowMessage">
                    <button><i class="fa fa-spinner fa-spin"></i> </button>
                  </template>
                  
                </form>
                @endif
            </main>   
            



<!-- Main modal -->
<template x-if="showHistory">
<div tabindex="-1" aria-hidden="true"
 class="border fixed" style="margin: auto; width: 30%; height: 100%; right: 0px;"
 >
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
        
            <!-- Modal body -->
            <div class="p-4 md:p-5">
                @foreach ( $chat_history as $k => $v )
                  <div x-data="{open_hist:false}" class="w-[60vw] mx-auto bg-red-50 mt-16">
                    <div  @click="open_hist=!open_hist" class="flex justify-left items-center bg-red-200" style="cursor: pointer;">
                      <button  class="px-2 text-black hover:text-gray-500 font-bold text-3xl">
                        <i x-show="!open_hist" class="fa-2xs fa-solid fa-chevron-right"></i>
                        <i x-show="open_hist" class="fa-2xs fa-solid fa-chevron-down"></i>
                      </button> 
                      <p class="px-1">{{ ucwords(str_replace("_", " ", $k)) }}</p>
                    </div>
                    <div x-show="open_hist"  x-cloak  class="mx-4 py-4" x-transition>
                        @foreach ($v as $d)
                          <div x-data="{open_hist_menu:false, show_rename: false, chatNewName: '{{ $d['question'] }}', chatUpdatedName: '{{ $d['question'] }}', show_loader: false, show_del_loader: false}">
                            <div class="flex flex-row justify-between">
                              <span @click="loadChat({{ $d['id'] }})" class="px-2" style="cursor: pointer;"><i class="fa-2xs fa-solid fa-minus"></i>&nbsp;&nbsp;&nbsp;<span x-html="chatUpdatedName"></span></span>
                              <div class="flex flex-row" >
                                <button @click="open_hist_menu=!open_hist_menu"><i class="fa-2xs fa-solid fa-ellipsis"></i></button>
                              </div>
                            </div>
                            <div  x-show="open_hist_menu" class="grid grid-cols-3 gap-3 " style="justify-items: end;" x-transition>
                              <div> </div>
                              <div> </div>
                              <div class="border"> 
                              <div @click="openShareForm({{ $d['id'] }}, '{{ $d['question'] }}')" class="flex flex-row px-2">
                                <p class="text-center" style="cursor: pointer">Share&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                <button>
                                  <i x-show="!showShare" class="fa-2xs fa-solid fa-share"></i>
                                  <i x-show="showShare" class="fa fa-spinner fa-spin"></i>
                                </button>
                              </div>
                              <div @click="show_rename = !show_rename" class="flex flex-row px-2" style="cursor: pointer;">
                                <div>Rename&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                                <button ><i class="fa-2xs fa-solid fa-pen"></i> </button>
                              </div>
                              <div x-show="show_rename" class="flex flex-row px2-2 change-chat-name">
                                <input x-model="chatNewName" style="appearance: none; border-style: none; line-height: 1; border-bottom: 1px solid gray !important; max-width: 60%;" class="mr-3 py-1 px-2 focus:outline-none" type="text" :value="chatNewName">
                                <button @click="triggerRename('{{ $d['id'] }}')">
                                  <i x-show="!show_loader" class="fa fa-solid fa-check" style="color: red"></i>
                                  <i x-show="show_loader" class="fa fa-spinner fa-spin" style="color: #4ade80"></i> 
                                </button>&nbsp;&nbsp;&nbsp;
                                <button @click="show_rename = false"><i class="fa fa-solid fa-xmark" style="color: #4ade80"></i> </button>
                              </div>
                              <div @click="triggerDelete('{{ $d['id'] }}', false)" class="flex flex-row px-2" style="cursor: pointer;">
                                <div>Delete Chat&nbsp;&nbsp;&nbsp;</div>
                                <button>
                                  <i x-show="!show_del_loader" class="fa-2xs fa-solid fa-trash"></i>
                                  <i x-show="show_del_loader" class="fa fa-spinner fa-spin" style="color: #4ade80"></i> 
                                </button>
                              </div>
                            </div>
                            </div>
                          </div>
                            
                        @endforeach
                    </div>
                    <hr class="h-[0.1rem] bg-slate-500 mt-2 mb-2">
                  </div>
                @endforeach
            </div>

        </div>
    </div>
</div> 
</template>

@if(!isset($chat_hash))
<template x-if="showShare">
  <div tabindex="-1" aria-hidden="true" class="justify-center items-center" style="position: absolute; left: 50%; top: 20%; transform: translate(-50%, -20%); z-index: 1000; min-width: 75vw;">
    <div x-data="{'copied': false}" class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h4 class="">
                    Share link to chat
                </h4>
                <button @click="showShare = !showShare" type="button" class="" data-modal-hide="static-modal">
                    <i class="fa-solid fa-xmark"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4">
                <div class="flex flex-row">
                    <p ><span x-html="shareChatName"></span>&nbsp;&nbsp;</p>
                    <!-- <span style="cursor: pointer;"><i class="fa-xs fa-solid fa-pen"></i></span> -->
                </div>
                <div style="height: 40vh; border:1px solid gray; display: flex; flex-direction: column;">
                  <div x-html="shareMessages" style="height: 90%; overflow-y: auto;">
                      
                  </div>
                  <div style="border-top:1px solid gray; padding: 2 px;">
                    <p>{{ auth()->user()->name }}, <span x-html="(new Date()).toDateString()"> </span></p>
                  </div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex justify-items-center justify-between items-center" >
              <div class="p-4 md:p-5 space-y-4" style="margin-top: 10px">
                <input @click="markAsSharableName($el)" :checked="shareNameAlready" type="checkbox" value="yes" /> Share your name
              </div>
              <div style="margin: 20px">
                <button @click="copyToClipboard('{{ env('APP_URL') }}' + 'sharable/' + shareHash)">
                  <i x-show="!copied" class="fa-xs fa-solid fa-link"></i>
                  <i x-show="copied" class="fa-xs fa-solid fa-check-double" style="color: #4ade80"></i>
                  Copy Link
                </button> 
              </div>
            </div>
        </div>
    </div>
  </div>
</template>
@endif
         
        </div>

      <script>
        var apiUrl = "{{ env('APP_URL') }}";
        function chatApp() {
          return {
              messages: JSON.parse(localStorage.getItem("messages")) || [],
              newMessage: "",
              newComment: "",
              userName: "Admin",
              selectedPdf: [],
              allowMessage: true,
              darkMode: false,
              showHistory: false,
              showSectionMenu: false,
              filter: "all",
              referenceDivs: "",
              showShare: false,
              shareMessages: "",
              shareChatName: "",
              shareNameAlready: false,
              shareChatId: "",
              shareHash: "",
              isNameShared: false,
              copied: false,
              adminview: true,
              dislike_ar: false,

    
              loadTheme() {
                //this.darkMode = localStorage.getItem("darkMode") === "true";
                this.darkMode = false;
                this.messages = [];
                let chatId = localStorage.getItem("master_chat_id");
                @if(isset($chat_hash))
                  this.loadMsgByChatHash("{{ $chat_hash }}")
                @else
                console.log(chatId)
                  if(!chatId) {
                    this.startNewChat();
                  } else {
                      var currentUrl = "{{ url()->current() }}";
                      let url = new URL(currentUrl);
                      let id = url.pathname.split('/')[2];
                      console.log('ID:', id);
                      if(id != undefined) {this.loadMsgByChatId(id); this.adminview = false;}
                      else this.loadMsgByChatId(chatId);
                     // console.log( url.pathname.split('/')[1] );
                      if(url.pathname.split('/')[1] == "dislike") this.dislike_ar = true;
                  }
                @endif
                this.cleanupAfterMessage();
              },
              triggerDelete(chatId, archive) {
                this.show_del_loader = true
                fetch(apiUrl + "delete-chat", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({chat_id: `${chatId}`, archive: `${archive}`}),
                    credentials: "include"
                })
                .then((response) => response.json())
                .then( (json) => {
                  this.show_del_loader = false
                  window.location.reload();
                })
                  .catch((err) => {
                    this.show_del_loader = false
                  });
                
              },
              markAsSharableName(elem) {
                let chatId = this.shareChatId;

                let markAsShared = "no";
                if(elem.checked)  {
                  markAsShared = "yes";
                }
                fetch(apiUrl + "mark-name-sharable", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({chat_id: `${chatId}`, share_name: `${markAsShared}`}),
                    credentials: "include"
                })
                .then((response) => response.json())
                  .catch((err) => {
                      console.log(err);
                  });
              },
              openShareForm(chatId, name) {
                fetch(apiUrl + "get-chat/" + chatId, {
                    method: 'GET',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .then((json) => {
                      let respText = "";
                      if(json['success'] == true) {
                        this.shareMessages = ""
                        this.shareChatName = name
                        this.shareChatId = chatId
                        
                        let shareHtml = "<div>"
                          
                        json.data.forEach(d => {
                          this.shareNameAlready = (d.share_name == 'yes') ? true : false;
                          this.shareHash = d.sharable_link
                          d.user_chats.forEach( ch => {
                            shareHtml += "<div style='padding-bottom: 2px'><p> Q: " + ch.question + " </p> <p style='padding-top: 2px;'>A: " + ch.answer + "</p></div><hr/>"
                          })
                        });
                        shareHtml += "</div>"
                        this.shareMessages = shareHtml
                        this.showShare = !this.showShare
                        this.showHistory = !this.showHistory
                      } else {
                        respText = "Error: " + json['message'];
                        setTimeout(function() {
                          this.setResponse(`${respText}`)
                        }.bind(this), 200);
                        this.showShare = !this.showShare
                        this.showHistory = !this.showHistory
                      }
                    }).catch((err) => {
                      this.cleanupAfterMessage();
                      this.showShare = !this.showShare
                      this.showHistory = !this.showHistory
                      console.log(err);
                  });

                
              },
              setSelected(spdf) {
                console.dir(spdf)
              },
              reLoadCurrentChat() {
                let chatId = localStorage.getItem("master_chat_id");
                if(!chatId) {
                  this.startNewChat();
                } else {
                  this.loadMsgByChatId(chatId)
                }
              },
              loadChat(chatId) {
                localStorage.setItem("master_chat_id", chatId);
                this.reLoadCurrentChat();
              },
              loadMsgByChatHash(chatHash) {
                
                fetch(apiUrl + "get-chat-by-hash/" + chatHash, {
                    method: 'GET',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .then((json) => {
                      let respText = "";
                      if(json['success'] == true) {
                        this.messages = []
                        json.data.forEach(d => {
                          d.user_chats.forEach( ch => {
                            let refer = {};
                            ch.pdfs.forEach( pd => {
                              if(!(pd.section.name in refer)) {
                                refer[pd.section.name] = [];
                              }
                              refer[pd.section.name].push(pd.name);
                            })
                           

                            respText = ch.question;
                            this.setUserResponse(`${respText}`)
                            respText = ch.answer;
                            this.setResponse(`${respText}`, refer)
                          })
                        });
                      } else {
                        respText = "Error: " + json['message'];
                        setTimeout(function() {
                          this.setResponse(`${respText}`)
                        }.bind(this), 1000);
                      }
                    }).catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });
              },
              loadMsgByChatId(chatId) {
                fetch(apiUrl + "get-chat/" + chatId, {
                    method: 'GET',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .then((json) => {
                      let respText = "";
                      if(json['success'] == true) {
                        this.messages = []
                        json.data.forEach(d => {
                          d.user_chats.forEach( ch => {
                            let refer = {};
                            ch.pdfs.forEach( pd => {
                              if(!(pd.section.name in refer)) {
                                refer[pd.section.name] = [];
                              }
                              refer[pd.section.name].push(pd.name);
                            })
                           

                            respText = ch.question;
                            this.setUserResponse(`${respText}`)
                            respText = ch.answer;
                            this.setResponse(`${respText}`, refer)
                          })
                        });
                      } else {
                        respText = "Error: " + json['message'];
                        setTimeout(function() {
                          this.setResponse(`${respText}`)
                        }.bind(this), 1000);
                      }
                    }).catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });
              },
              handleScroll() {
                this.referenceDivs = ""
                
                let elements = document.getElementsByClassName('ai-response-reference');
                for (let i = 0; i < elements.length; i++) {
                  this.addReferenceDiv(elements[i], true);
                }
              },
              addReferenceDiv(elem, createRef, ref = "") {
                if(!createRef) {
                  return;
                }
                let height = elem.getBoundingClientRect().height;
                let top = elem.getBoundingClientRect().top;

                let vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)
                
                if(top > vh - height || top < 40) {
                  return;
                }
                if(ref == "") {
                 ref = elem.getAttribute("reference");
                }
                
                let append = '<div class="mobile-avoid" style="position: absolute; top: ' + top + 'px; left: 10px; width: 18%; padding: 20px; min-height: ' + height +'px; border: 1px solid #e5e5ea; background: #e5e5ea;  border-radius: 5px; overflow: auto;"><span style="margin: 2px;">' + ref + '</span></div>'
                this.referenceDivs += append;
              },
              copyToClipboard(copyText) {
                this.copied = true
                navigator.clipboard.writeText(copyText);
                setTimeout(function() {
                  this.copied = false
                }.bind(this), 800);
              },
              shareData(text) {
                let url = 'https://twitter.com/intent/tweet?text=' + text + "&url=" + "{{env('APP_URL')}}";
                TwitterWindow = window.open(url, "{{ env('APP_NAME') }}", width=600, height=300);
                return false;
              },
              scroll() {
                const theDiv = document.querySelector('#chat-div');
                let scrollHeight = Math.max(theDiv.scrollHeight, theDiv.clientHeight);
                theDiv.scrollTop = scrollHeight + theDiv.clientHeight;
              },
              cleanupAfterMessage() {
                this.allowMessage = true;
                setTimeout(function() {
                  this.scroll();
                }.bind(this), 1000);
              },
              toggleDarkMode() {
                this.darkMode = false;
                /*this.darkMode = !this.darkMode;
                localStorage.setItem("darkMode", this.darkMode);*/
              },
    
              sendMessage() {
                if(!this.newMessage.trim() || !this.allowMessage) {
                  return;
                }
                this.allowMessage = false;
                let quest = "";
                if(!this.is_reply) {
                  this.is_reply = true; 
                  this.setUserResponse(this.replymessage + "<br />" + this.newMessage);
                  quest = this.newMessage + "\n" + this.replymessage;
                  this.replymessage = "";
                }
                else {
                  this.setUserResponse(this.newMessage);
                   quest = this.newMessage;
                }
                localStorage.setItem("newMessage", this.newMessage);
                this.newMessage = ""; 
                // Simulate a response
                if(this.selectedPdf.length <= 0) {
                  setTimeout(function() {
                    this.setResponse(`Select Code before asking question`)
                    this.cleanupAfterMessage();
                  }.bind(this), 1000);
                } else {
                  this.send_message(quest);
                }
              },
              send_message(quest) {
                fetch(apiUrl + "send-message", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({question: `${quest}`, master_chat_id: localStorage.getItem("master_chat_id"), selected_pdf: this.selectedPdf}),
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .then((json) => {
                      let respText = "";
                      if(json['success'] == true) {
                        respText = json['message'];
                        localStorage.setItem("user_chat_id", json['user_chat_id']);
                      } else {
                        respText = "Error: " + json['message'];
                      }

                      setTimeout(function() {
                        this.setResponse(`${respText}`, json['data'])
                      }.bind(this), 1000);
                    }).catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });
              },
              reloadAnswer() {
                this.send_message(localStorage.getItem("newMessage"));
              },
              replyAnswer(msg) {
                this.is_reply = !this.is_reply;
                this.replymessage = msg;
              },
              reply() {
                return this.replymessage;
              },
              setResponse(msg, refer = []) {
                let setRefer = "";
                if(refer.length <= 0) {
                  setRefer = "No Code were selected"
                } else {
                  
                  for (const key of Object.keys(refer)) {
                    setRefer += key + " (";
                    refer[key].forEach( p => {
                      setRefer += p + ","
                    })
                    setRefer += ") <br/>";
                  }
                }
                const response = {
                  id:  "resp" + Date.now().toString(36) + Math.random().toString(36).substr(2),
                  text: `${msg}`,
                  type: "response",
                  reference: setRefer,
                };
                
                this.messages.push(response);
                localStorage.setItem("messages", JSON.stringify(this.messages));
                this.cleanupAfterMessage();
              },
              setUserResponse(msg) {
                const response = {
                  id: "user" + Date.now().toString(36) + Math.random().toString(36).substr(2),
                  text: `${msg}`,
                  type: "user",
                  reference: "",
                };
                this.messages.push(response);
                localStorage.setItem("messages", JSON.stringify(this.messages));
                //this.cleanupAfterMessage();
              },
    
              startNewChat() {
                localStorage.removeItem("messages");
                fetch(apiUrl + "start-chat", {
                  method: 'GET',
                  credentials: "include"
                })
                  .then((response) => response.json())
                  .then((json) => {
                    this.messages = [];
                    let text = ""
                    
                    if(json['success'] == true) {
                      localStorage.setItem("master_chat_id", json['data']['id']);
                      text = "Select Code before asking questions";
                    } else {
                      text = "Error: " + json['message'];
                    }
                    setTimeout(function() {
                      this.setResponse(`${text}`)
                    }.bind(this), 1000);
                  }).catch((err) => {
                    console.log(err);
                });
              },

              get loadMessages() {
                return this.messages;
              },

               dislike() {
                if(this.is_dislike == 1) this.is_dislike = 0;
                else this.is_dislike = 1;
              },

              sendDislike() {
                if(!this.newComment.trim()){
                    return;
                  }

                  this.is_dislike = 2;

                  fetch(apiUrl + "dislike", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({comment: `${this.newComment}`, chat_id: localStorage.getItem("user_chat_id")}),
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .then((json) => {
                      let respText = "";
                      if(json['success'] == true) {
                        respText = json['message'];
                      } else {
                        respText = "Error: " + json['message'];
                      }

                      setTimeout(function() {
                        this.setResponse(`${respText}`, json['data'])
                      }.bind(this), 1000);
                    }).catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });
              },
              reTrain(chatId) {
                let reference = "";
                let messages = JSON.parse(localStorage.getItem("messages"));
                for(i=0; i<messages.length; i++)
                {
                  if(messages[i].type == 'response' && reference.indexOf(messages[i].reference.split('(')[1].split(')')[0]) < 0)
                    reference += messages[i].reference.split('(')[1].split(')')[0];
                }
                console.log(reference);
                fetch(apiUrl + "retrain", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({pdfs: reference}),
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });
              },

              reject() {
                var currentUrl = "{{ url()->current() }}";
                      let url = new URL(currentUrl);
                      let id = url.pathname.split('/')[2];
                fetch(apiUrl + "reject", {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({masterId: id}),
                    credentials: "include"
                  })
                    .then((response) => response.json())
                    .catch((err) => {
                      this.cleanupAfterMessage();
                      console.log(err);
                  });

              },

          };
        }
      </script>
</x-app-layout>
