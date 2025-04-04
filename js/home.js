document.addEventListener('DOMContentLoaded', () => {
    const communitiesList = document.querySelector('.communities ul');

    // Sample community data
    const communities = [
        '1XC3',
        '1XD3',
        '1MD3',
        '1JC3'
    ];

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
        pageTitle.textContent = `Class: ${community}`;
    }
});