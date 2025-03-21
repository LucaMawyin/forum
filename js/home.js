document.addEventListener('DOMContentLoaded', () => {
    const homeContent = document.querySelector('.home-content');
    const communitiesList = document.querySelector('.communities ul');

    // Sample post data
    const posts = [
        {
            title: 'This is a Title 1',
            content: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sagittis augue lectus...',
            link: 'post1.html'
        },
        {
            title: 'This is a Title 2',
            content: 'Phasellus condimentum mauris ut magna efficitur cursus. Nulla facilisi...',
            link: 'post2.html'
        },
        {
            title: 'This is a Title 3',
            content: 'Aenean porttitor mattis bibendum. Duis varius ipsum risus, eu pretium diam...',
            link: 'post3.html'
        }
    ];

    // Sample community data
    const communities = [
        '1XC3',
        '1XD3',
        '1MD3',
        '1JC3'
    ];

    // Creating post tile
    function createPostTile(post) {
        const postLink = document.createElement('a');
        postLink.href = post.link;
        postLink.classList.add('post-link');

        const postTile = document.createElement('div');
        postTile.classList.add('post-tile');

        const postTitle = document.createElement('h1');
        postTitle.textContent = post.title;
        
        const postContent = document.createElement('p');
        postContent.textContent = post.content;

        // Add title and content to post tile
        postTile.appendChild(postTitle);
        postTile.appendChild(postContent);

        // Add post tile to link
        postLink.appendChild(postTile);

        // Append post link to home content
        homeContent.appendChild(postLink);
    }

    // Generating tiles for each post
    posts.forEach(createPostTile);

    // Create community links dynamically with query parameters
    function createCommunityLink(community) {
        const listItem = document.createElement('li');
        const communityLink = document.createElement('a');

        // Query parameter
        communityLink.href = `community.html?community=${community}`;
        communityLink.textContent = community;

        listItem.appendChild(communityLink);
        communitiesList.appendChild(listItem);
    }

    // Generate links
    communities.forEach(createCommunityLink);

    // Query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const community = urlParams.get('community');
    
    // Changing header title
    if (community) {
        const pageTitle = document.querySelector('h1');
        pageTitle.textContent = `Community: ${community}`;
    }
});