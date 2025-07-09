import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import axios from '@nextcloud/axios'

// Element Web Integration
class ElementMatrixClient {
    constructor() {
        this.baseUrl = generateUrl('/apps/elementmatrix')
        this.matrixClient = null
        this.elementUrl = loadState('elementmatrix', 'element_url')
        this.init()
    }

    async init() {
        // Initialize Matrix client
        await this.initMatrixClient()
        
        // Setup UI
        this.setupUI()
        
        // Setup event listeners
        this.setupEventListeners()
    }

    async initMatrixClient() {
        try {
            const response = await axios.get(this.baseUrl + '/api/v1/config')
            this.config = response.data
            
            // Initialize Element Web in iframe
            this.setupElementIframe()
        } catch (error) {
            console.error('Failed to initialize Matrix client:', error)
        }
    }

    setupElementIframe() {
        const iframe = document.createElement('iframe')
        iframe.src = this.elementUrl
        iframe.style.width = '100%'
        iframe.style.height = '100vh'
        iframe.style.border = 'none'
        
        // Add Element Web to Nextcloud Talk container
        const container = document.getElementById('app-content')
        if (container) {
            container.appendChild(iframe)
        }
    }

    setupUI() {
        // Add ElementMatrix to navigation
        const nav = document.querySelector('#app-navigation')
        if (nav) {
            const elementMatrixNav = document.createElement('li')
            elementMatrixNav.innerHTML = `
                <a href="#" class="nav-elementmatrix">
                    <img class="app-navigation-entry-icon" src="${generateUrl('/apps/elementmatrix/img/app.svg')}" alt="ElementMatrix">
                    <span class="app-navigation-entry-title">ElementMatrix</span>
                </a>
            `
            nav.appendChild(elementMatrixNav)
        }
    }

    setupEventListeners() {
        // Listen for Nextcloud file sharing events
        document.addEventListener('fileShared', (event) => {
            this.handleFileShare(event.detail)
        })

        // Listen for calendar events
        document.addEventListener('calendarEventCreated', (event) => {
            this.handleCalendarEvent(event.detail)
        })

        // Listen for contact invitations
        document.addEventListener('contactInvite', (event) => {
            this.handleContactInvite(event.detail)
        })
    }

    async handleFileShare(shareData) {
        try {
            await axios.post(this.baseUrl + '/api/v1/rooms/' + shareData.roomToken + '/share', {
                shareToken: shareData.shareToken,
                path: shareData.path
            })
        } catch (error) {
            console.error('Failed to share file:', error)
        }
    }

    async handleCalendarEvent(eventData) {
        try {
            await axios.post(this.baseUrl + '/api/v1/calendar/events', eventData)
        } catch (error) {
            console.error('Failed to create calendar event:', error)
        }
    }

    async handleContactInvite(contactData) {
        try {
            await axios.post(this.baseUrl + '/api/v1/rooms/' + contactData.roomToken + '/participants', {
                newParticipant: contactData.contactId
            })
        } catch (error) {
            console.error('Failed to invite contact:', error)
        }
    }

    // Nextcloud Talk API compatibility methods
    async createRoom(roomName, type = 2) {
        try {
            const response = await axios.post(this.baseUrl + '/api/v1/room', {
                roomName: roomName,
                roomType: type
            })
            return response.data
        } catch (error) {
            console.error('Failed to create room:', error)
            throw error
        }
    }

    async getRooms() {
        try {
            const response = await axios.get(this.baseUrl + '/api/v1/rooms')
            return response.data
        } catch (error) {
            console.error('Failed to get rooms:', error)
            throw error
        }
    }

    async sendMessage(roomToken, message) {
        try {
            const response = await axios.post(this.baseUrl + '/api/v1/chat/' + roomToken, {
                message: message
            })
            return response.data
        } catch (error) {
            console.error('Failed to send message:', error)
            throw error
        }
    }

    async getMessages(roomToken, lookIntoFuture = 0, limit = 20) {
        try {
            const response = await axios.get(this.baseUrl + '/api/v1/chat/' + roomToken, {
                params: {
                    lookIntoFuture: lookIntoFuture,
                    limit: limit
                }
            })
            return response.data
        } catch (error) {
            console.error('Failed to get messages:', error)
            throw error
        }
    }
}

// Initialize ElementMatrix when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ElementMatrixClient()
})
